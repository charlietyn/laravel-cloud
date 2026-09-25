<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Unit;

use Ronu\LaravelCloud\Support\AwsClientConfiguration;
use Ronu\LaravelCloud\Tests\TestCase;

final class AwsClientConfigurationTest extends TestCase
{
    public function test_credentials_are_optional_for_iam_based_production(): void
    {
        $config = AwsClientConfiguration::fromArray(['region' => 'eu-west-2', 'credentials' => [], 's3' => []]);
        self::assertNull($config->credentials());
        self::assertNull($config->endpoint);
    }

    public function test_custom_endpoint_and_dummy_credentials_are_supported_for_floci(): void
    {
        $config = AwsClientConfiguration::fromArray([
            'region' => 'eu-west-2',
            'endpoint' => 'http://floci:4566',
            'credentials' => ['key' => 'test', 'secret' => 'test'],
            's3' => ['path_style' => true],
        ]);
        self::assertSame('http://floci:4566', $config->endpoint);
        self::assertTrue($config->s3PathStyle);
        self::assertSame('test', $config->credentials()['key']);
    }
}
