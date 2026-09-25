<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Contract;

use Ronu\LaravelCloud\Tests\TestCase;
use Illuminate\Config\Repository;
use PHPUnit\Framework\SkippedWithMessageException;

abstract class ContractTestCase extends TestCase
{
    protected string $target;

    protected function defineEnvironment($app): void
    {
        $this->target = getenv('CLOUD_CONTRACT_TARGET') ?: 'floci';
        /** @var Repository $config */
        $config = $app['config'];
        $config->set('cloud.region', getenv('CLOUD_CONTRACT_REGION') ?: 'eu-west-2');

        if ($this->target === 'aws') {
            $config->set('cloud.endpoint', null);
            $config->set('cloud.credentials.key', null);
            $config->set('cloud.credentials.secret', null);
            $config->set('cloud.s3.path_style', false);
        } else {
            $config->set('cloud.endpoint', getenv('AWS_ENDPOINT_URL') ?: 'http://localhost.floci.io:4566');
            $config->set('cloud.credentials.key', 'test');
            $config->set('cloud.credentials.secret', 'test');
            $config->set('cloud.s3.path_style', true);
        }

        $bucket = getenv('CLOUD_CONTRACT_S3_BUCKET') ?: 'laravel-cloud-contracts';
        $queue = getenv('CLOUD_CONTRACT_SQS_QUEUE') ?: 'laravel-cloud-contracts';
        $bus = getenv('CLOUD_CONTRACT_EVENT_BUS') ?: 'laravel-cloud-contracts';
        $table = getenv('CLOUD_CONTRACT_DYNAMODB_TABLE') ?: 'laravel-cloud-contracts';

        $config->set('cloud.s3.bucket', $bucket);
        $config->set('cloud.queues.default', $queue);
        $config->set('cloud.event_buses.default', $bus);
        $config->set('cloud.documents.collections.default.table', $table);
        $config->set('cloud.bootstrap.buckets', [$bucket]);
        $config->set('cloud.bootstrap.queues', [$queue]);
        $config->set('cloud.bootstrap.event_buses', [$bus]);
        $config->set('cloud.bootstrap.document_tables', [['table' => $table, 'id_attribute' => 'id']]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->target === 'aws') {
            if (getenv('CLOUD_CONTRACT_ALLOW_AWS') !== 'true') {
                $this->markTestSkipped('Set CLOUD_CONTRACT_ALLOW_AWS=true to execute the same contract suite against an AWS sandbox.');
            }
            return;
        }

        $exit = $this->artisan('cloud:bootstrap')->run();
        self::assertSame(0, $exit);
    }
}
