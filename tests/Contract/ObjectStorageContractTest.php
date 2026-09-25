<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Contract;

use Ronu\LaravelCloud\Contracts\ObjectStorage;

final class ObjectStorageContractTest extends ContractTestCase
{
    public function test_object_storage_behavior_is_portable(): void
    {
        $storage = $this->app->make(ObjectStorage::class);
        $key = 'contracts/'.bin2hex(random_bytes(8)).'.txt';
        $storage->put($key, 'portable', 'text/plain');
        self::assertTrue($storage->exists($key));
        self::assertSame('portable', $storage->get($key));
        self::assertSame(8, $storage->metadata($key)->size);
        $storage->delete($key);
        self::assertFalse($storage->exists($key));
    }
}
