<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Support;

use Aws\DynamoDb\DynamoDbClient;
use Aws\EventBridge\EventBridgeClient;
use Aws\Lambda\LambdaClient;
use Aws\S3\S3Client;
use Aws\SecretsManager\SecretsManagerClient;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;

final readonly class AwsClientFactory
{
    public function __construct(private AwsClientConfiguration $configuration) {}

    public function s3(): S3Client
    {
        return new S3Client($this->config([
            'use_path_style_endpoint' => $this->configuration->s3PathStyle,
        ]));
    }

    public function sqs(): SqsClient { return new SqsClient($this->config()); }
    public function sns(): SnsClient { return new SnsClient($this->config()); }
    public function eventBridge(): EventBridgeClient { return new EventBridgeClient($this->config()); }
    public function dynamoDb(): DynamoDbClient { return new DynamoDbClient($this->config()); }
    public function secretsManager(): SecretsManagerClient { return new SecretsManagerClient($this->config()); }
    public function lambda(): LambdaClient { return new LambdaClient($this->config()); }

    /** @param array<string, mixed> $overrides @return array<string, mixed> */
    private function config(array $overrides = []): array
    {
        $config = [
            'version' => 'latest',
            'region' => $this->configuration->region,
            'http' => [
                'connect_timeout' => $this->configuration->connectTimeoutSeconds,
                'timeout' => $this->configuration->timeoutSeconds,
            ],
            'retries' => $this->configuration->retries,
        ];

        if ($this->configuration->endpoint !== null) {
            $config['endpoint'] = $this->configuration->endpoint;
        }

        if (($credentials = $this->configuration->credentials()) !== null) {
            $config['credentials'] = $credentials;
        }

        return array_replace($config, $overrides);
    }
}
