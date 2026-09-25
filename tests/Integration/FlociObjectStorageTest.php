<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Integration;

use Ronu\LaravelCloud\Contracts\ObjectStorage;
use Ronu\LaravelCloud\Tests\FlociIntegrationTestCase;

final class FlociObjectStorageTest extends FlociIntegrationTestCase
{
    public function test_real_s3_adapter_round_trip_against_floci(): void
    {
        $storage = $this->app->make(ObjectStorage::class);
        $key = 'integration/'.bin2hex(random_bytes(6)).'.txt';
        $storage->put($key, 'hello-floci', 'text/plain');
        self::assertTrue($storage->exists($key));
        self::assertSame('hello-floci', $storage->get($key));
        self::assertSame(11, $storage->metadata($key)->size);
        $storage->delete($key);
        self::assertFalse($storage->exists($key));
    }
}
