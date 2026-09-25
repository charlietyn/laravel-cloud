<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests;

use Illuminate\Config\Repository;

abstract class FlociIntegrationTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        /** @var Repository $config */
        $config = $app['config'];
        $config->set('cloud.region', getenv('AWS_DEFAULT_REGION') ?: 'eu-west-2');
        $config->set('cloud.endpoint', getenv('AWS_ENDPOINT_URL') ?: 'http://localhost.floci.io:4566');
        $config->set('cloud.credentials.key', getenv('AWS_ACCESS_KEY_ID') ?: 'test');
        $config->set('cloud.credentials.secret', getenv('AWS_SECRET_ACCESS_KEY') ?: 'test');
        $config->set('cloud.s3.path_style', true);
        $config->set('cloud.s3.bucket', 'laravel-cloud-tests');
        $config->set('cloud.queues.default', 'laravel-cloud-tests');
        $config->set('cloud.topics.default', 'laravel-cloud-tests');
        $config->set('cloud.event_buses.default', 'laravel-cloud-tests');
        $config->set('cloud.secrets.default', 'laravel-cloud-tests');
        $config->set('cloud.documents.collections.default.table', 'laravel-cloud-tests');
        $config->set('cloud.functions.default', 'laravel-cloud-tests');
        $config->set('cloud.bootstrap.buckets', ['laravel-cloud-tests']);
        $config->set('cloud.bootstrap.queues', ['laravel-cloud-tests']);
        $config->set('cloud.bootstrap.topics', ['laravel-cloud-tests']);
        $config->set('cloud.bootstrap.event_buses', ['laravel-cloud-tests']);
        $config->set('cloud.bootstrap.document_tables', [['table' => 'laravel-cloud-tests', 'id_attribute' => 'id']]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $exit = $this->artisan('cloud:bootstrap')->run();
        self::assertSame(0, $exit, 'Floci bootstrap failed. Is docker compose -f docker-compose.floci.yml up -d running?');
    }
}
