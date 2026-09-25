<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Laravel\Commands;

use Ronu\LaravelCloud\Support\AwsClientConfiguration;
use Ronu\LaravelCloud\Support\AwsClientFactory;
use Illuminate\Console\Command;
use Throwable;

final class CloudDoctorCommand extends Command
{
    protected $signature = 'cloud:doctor';
    protected $description = 'Check cloud configuration and AWS-compatible service connectivity without exposing secrets.';

    public function __construct(
        private readonly AwsClientConfiguration $configuration,
        private readonly AwsClientFactory $clients,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $checks = [
            'S3' => fn () => $this->clients->s3()->listBuckets(),
            'SQS' => fn () => $this->clients->sqs()->listQueues(),
            'SNS' => fn () => $this->clients->sns()->listTopics(),
            'EventBridge' => fn () => $this->clients->eventBridge()->listEventBuses(),
            'DynamoDB' => fn () => $this->clients->dynamoDb()->listTables(),
            'Secrets Manager' => fn () => $this->clients->secretsManager()->listSecrets(['MaxResults' => 1]),
            'Lambda' => fn () => $this->clients->lambda()->listFunctions(['MaxItems' => 1]),
        ];

        $rows = [
            ['Region', $this->configuration->region],
            ['Endpoint', $this->configuration->endpoint ?? '[AWS default endpoints]'],
            ['Credentials', $this->configuration->accessKey === null ? '[AWS default credential chain]' : '[configured; hidden]'],
        ];

        $failed = false;
        foreach ($checks as $service => $check) {
            try {
                $check();
                $rows[] = [$service, 'OK'];
            } catch (Throwable $e) {
                $failed = true;
                $rows[] = [$service, 'FAIL: '.$e->getMessage()];
            }
        }

        $this->table(['Cloud configuration', 'Status'], $rows);

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
