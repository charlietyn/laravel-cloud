<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Testing\Fakes;

use Ronu\LaravelCloud\Contracts\ObjectStorage;
use Ronu\LaravelCloud\DTO\ObjectMetadata;
use Ronu\LaravelCloud\Exceptions\ObjectStorageException;
use DateTimeImmutable;

final class FakeObjectStorage implements ObjectStorage
{
    /** @var array<string, array{contents:string,content_type:?string,metadata:array<string,string>,updated_at:DateTimeImmutable}> */
    private array $objects = [];

    public function put(string $key, string $contents, ?string $contentType = null, array $metadata = []): void
    {
        $this->objects[$key] = ['contents' => $contents, 'content_type' => $contentType, 'metadata' => $metadata, 'updated_at' => new DateTimeImmutable()];
    }

    public function get(string $key): string
    {
        return $this->objects[$key]['contents'] ?? throw new ObjectStorageException("Object [{$key}] does not exist.");
    }

    public function delete(string $key): void { unset($this->objects[$key]); }
    public function exists(string $key): bool { return isset($this->objects[$key]); }

    public function temporaryUrl(string $key, DateTimeImmutable $expiresAt): string
    {
        if (! $this->exists($key)) throw new ObjectStorageException("Object [{$key}] does not exist.");
        return 'https://fake-storage.test/'.rawurlencode($key).'?expires='.$expiresAt->getTimestamp();
    }

    public function metadata(string $key): ObjectMetadata
    {
        $object = $this->objects[$key] ?? throw new ObjectStorageException("Object [{$key}] does not exist.");
        return new ObjectMetadata($key, strlen($object['contents']), $object['content_type'], $object['updated_at']);
    }
}
