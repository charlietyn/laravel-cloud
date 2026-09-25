<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Laravel;

use Ronu\LaravelCloud\Aws\DynamoDbDocumentStore;
use Ronu\LaravelCloud\Aws\EventBridgeEventBus;
use Ronu\LaravelCloud\Aws\LambdaFunctionInvoker;
use Ronu\LaravelCloud\Aws\S3ObjectStorage;
use Ronu\LaravelCloud\Aws\SecretsManagerSecretStore;
use Ronu\LaravelCloud\Aws\SnsTopicPublisher;
use Ronu\LaravelCloud\Aws\SqsMessageQueue;
use Ronu\LaravelCloud\Contracts\DocumentStore;
use Ronu\LaravelCloud\Contracts\EventBus;
use Ronu\LaravelCloud\Contracts\FunctionInvoker;
use Ronu\LaravelCloud\Contracts\MessageQueue;
use Ronu\LaravelCloud\Contracts\ObjectStorage;
use Ronu\LaravelCloud\Contracts\SecretStore;
use Ronu\LaravelCloud\Contracts\TopicPublisher;
use Ronu\LaravelCloud\Laravel\Commands\CloudBootstrapCommand;
use Ronu\LaravelCloud\Laravel\Commands\CloudDoctorCommand;
use Ronu\LaravelCloud\Support\AwsClientConfiguration;
use Ronu\LaravelCloud\Support\AwsClientFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\ServiceProvider;

final class CloudServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/cloud.php', 'cloud');

        $this->app->singleton(AwsClientConfiguration::class, function ($app): AwsClientConfiguration {
            /** @var ConfigRepository $config */
            $config = $app->make(ConfigRepository::class);

            return AwsClientConfiguration::fromArray((array) $config->get('cloud', []));
        });

        $this->app->singleton(AwsClientFactory::class);

        $this->app->singleton(ObjectStorage::class, function ($app): ObjectStorage {
            /** @var ConfigRepository $config */
            $config = $app->make(ConfigRepository::class);
            /** @var FilesystemManager $filesystems */
            $filesystems = $app->make(FilesystemManager::class);
            /** @var AwsClientConfiguration $aws */
            $aws = $app->make(AwsClientConfiguration::class);

            $disk = [
                'driver' => 's3',
                'region' => $aws->region,
                'bucket' => (string) $config->get('cloud.s3.bucket'),
                'use_path_style_endpoint' => $aws->s3PathStyle,
                'throw' => true,
            ];

            if ($aws->endpoint !== null) {
                $disk['endpoint'] = $aws->endpoint;
            }
            if ($aws->accessKey !== null && $aws->secretKey !== null) {
                $disk['key'] = $aws->accessKey;
                $disk['secret'] = $aws->secretKey;
                if ($aws->sessionToken !== null) {
                    $disk['token'] = $aws->sessionToken;
                }
            }

            return new S3ObjectStorage($filesystems->build($disk));
        });

        $this->app->singleton(MessageQueue::class, fn ($app): MessageQueue => new SqsMessageQueue(
            $app->make(AwsClientFactory::class)->sqs(),
            (array) $app->make(ConfigRepository::class)->get('cloud.queues', []),
        ));

        $this->app->singleton(EventBus::class, fn ($app): EventBus => new EventBridgeEventBus(
            $app->make(AwsClientFactory::class)->eventBridge(),
            (array) $app->make(ConfigRepository::class)->get('cloud.event_buses', []),
        ));

        $this->app->singleton(TopicPublisher::class, fn ($app): TopicPublisher => new SnsTopicPublisher(
            $app->make(AwsClientFactory::class)->sns(),
            (array) $app->make(ConfigRepository::class)->get('cloud.topics', []),
        ));

        $this->app->singleton(SecretStore::class, fn ($app): SecretStore => new SecretsManagerSecretStore(
            $app->make(AwsClientFactory::class)->secretsManager(),
            (array) $app->make(ConfigRepository::class)->get('cloud.secrets', []),
        ));

        $this->app->singleton(DocumentStore::class, fn ($app): DocumentStore => new DynamoDbDocumentStore(
            $app->make(AwsClientFactory::class)->dynamoDb(),
            (array) $app->make(ConfigRepository::class)->get('cloud.documents.collections', []),
        ));

        $this->app->singleton(FunctionInvoker::class, fn ($app): FunctionInvoker => new LambdaFunctionInvoker(
            $app->make(AwsClientFactory::class)->lambda(),
            (array) $app->make(ConfigRepository::class)->get('cloud.functions', []),
        ));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/cloud.php' => config_path('cloud.php'),
        ], 'laravel-cloud-config');

        if ($this->app->runningInConsole()) {
            $this->commands([CloudDoctorCommand::class, CloudBootstrapCommand::class]);
        }
    }
}
