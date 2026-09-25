<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Integration;

use Aws\Exception\AwsException;
use Ronu\LaravelCloud\Contracts\SecretStore;
use Ronu\LaravelCloud\Support\AwsClientFactory;
use Ronu\LaravelCloud\Tests\FlociIntegrationTestCase;

final class FlociSecretStoreTest extends FlociIntegrationTestCase
{
    public function test_real_secrets_manager_adapter_reads_against_floci(): void
    {
        $client = $this->app->make(AwsClientFactory::class)->secretsManager();
        try {
            $client->createSecret(['Name' => 'laravel-cloud-tests', 'SecretString' => 'initial']);
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() !== 'ResourceExistsException') throw $e;
            $client->putSecretValue(['SecretId' => 'laravel-cloud-tests', 'SecretString' => 'initial']);
        }
        self::assertSame('initial', $this->app->make(SecretStore::class)->get('default'));
    }
}
