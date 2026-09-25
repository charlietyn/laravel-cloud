<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Laravel\Commands;

use Aws\Exception\AwsException;
use Ronu\LaravelCloud\Support\AwsClientConfiguration;
use Ronu\LaravelCloud\Support\AwsClientFactory;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Throwable;

final class CloudBootstrapCommand extends Command
{
    protected $signature = 'cloud:bootstrap {--force : Allow bootstrap outside local/testing when an explicit custom endpoint is configured}';
    protected $description = 'Idempotently provision development/test resources against Floci or another private AWS-compatible endpoint.';

    public function __construct(
        private readonly Application $app,
        private readonly ConfigRepository $config,
        private readonly AwsClientConfiguration $configuration,
        private readonly AwsClientFactory $clients,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->safeEnvironment()) {
            $this->error('cloud:bootstrap is restricted to local/testing or an explicitly forced custom endpoint.');
            return self::FAILURE;
        }

        try {
            foreach ((array) $this->config->get('cloud.bootstrap.buckets', []) as $bucket) {
                if (is_string($bucket) && $bucket !== '') $this->ensureBucket($bucket);
            }
            foreach ((array) $this->config->get('cloud.bootstrap.queues', []) as $queue) {
                if (is_string($queue) && $queue !== '') $this->ensureQueue($queue);
            }
            foreach ((array) $this->config->get('cloud.bootstrap.topics', []) as $topic) {
                if (is_string($topic) && $topic !== '') $this->ensureTopic($topic);
            }
            foreach ((array) $this->config->get('cloud.bootstrap.event_buses', []) as $bus) {
                if (is_string($bus) && $bus !== '' && $bus !== 'default') $this->ensureEventBus($bus);
            }
            foreach ((array) $this->config->get('cloud.bootstrap.document_tables', []) as $table) {
                if (is_array($table) && isset($table['table'])) {
                    $this->ensureDocumentTable((string) $table['table'], (string) ($table['id_attribute'] ?? 'id'));
                }
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info('Cloud development resources are ready.');
        return self::SUCCESS;
    }

    private function safeEnvironment(): bool
    {
        if ($this->app->environment(['local', 'testing'])) {
            return true;
        }

        return (bool) $this->option('force') && $this->configuration->endpoint !== null;
    }

    private function ensureBucket(string $bucket): void
    {
        $s3 = $this->clients->s3();
        try {
            $s3->headBucket(['Bucket' => $bucket]);
        } catch (AwsException) {
            $args = ['Bucket' => $bucket];
            if ($this->configuration->region !== 'us-east-1') {
                $args['CreateBucketConfiguration'] = ['LocationConstraint' => $this->configuration->region];
            }
            $s3->createBucket($args);
        }
        $this->line("S3 bucket ............. {$bucket}");
    }

    private function ensureQueue(string $queue): void
    {
        $this->clients->sqs()->createQueue(['QueueName' => $queue]);
        $this->line("SQS queue .............. {$queue}");
    }

    private function ensureTopic(string $topic): void
    {
        $result = $this->clients->sns()->createTopic(['Name' => $topic]);
        $this->line('SNS topic .............. '.(string) $result->get('TopicArn'));
    }

    private function ensureEventBus(string $bus): void
    {
        $client = $this->clients->eventBridge();
        try {
            $client->describeEventBus(['Name' => $bus]);
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() !== 'ResourceNotFoundException') throw $e;
            $client->createEventBus(['Name' => $bus]);
        }
        $this->line("EventBridge bus ........ {$bus}");
    }

    private function ensureDocumentTable(string $table, string $idAttribute): void
    {
        $client = $this->clients->dynamoDb();
        try {
            $client->describeTable(['TableName' => $table]);
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() !== 'ResourceNotFoundException') throw $e;
            $client->createTable([
                'TableName' => $table,
                'BillingMode' => 'PAY_PER_REQUEST',
                'AttributeDefinitions' => [['AttributeName' => $idAttribute, 'AttributeType' => 'S']],
                'KeySchema' => [['AttributeName' => $idAttribute, 'KeyType' => 'HASH']],
            ]);
        }
        $this->line("DynamoDB table ......... {$table}");
    }
}
