<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Aws;

use Ronu\LaravelCloud\Contracts\ObjectStorage;
use Ronu\LaravelCloud\DTO\ObjectMetadata;
use Ronu\LaravelCloud\Exceptions\ObjectStorageException;
use DateTimeImmutable;
use Illuminate\Filesystem\FilesystemAdapter;
use Throwable;

final readonly class S3ObjectStorage implements ObjectStorage
{
    public function __construct(private FilesystemAdapter $filesystem) {}

    public function put(string $key, string $contents, ?string $contentType = null, array $metadata = []): void
    {
        try {
            $options = [];
            if ($contentType !== null) {
                $options['ContentType'] = $contentType;
            }
            if ($metadata !== []) {
                $options['Metadata'] = $metadata;
            }

            if (! $this->filesystem->put($key, $contents, $options)) {
                throw new ObjectStorageException("Unable to store object [{$key}].");
            }
        } catch (ObjectStorageException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ObjectStorageException("Unable to store object [{$key}].", 0, $e);
        }
    }

    public function get(string $key): string
    {
        try {
            $contents = $this->filesystem->get($key);
            if (! is_string($contents)) {
                throw new ObjectStorageException("Object [{$key}] did not return string contents.");
            }

            return $contents;
        } catch (ObjectStorageException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ObjectStorageException("Unable to read object [{$key}].", 0, $e);
        }
    }

    public function delete(string $key): void
    {
        try {
            if (! $this->filesystem->delete($key)) {
                throw new ObjectStorageException("Unable to delete object [{$key}].");
            }
        } catch (ObjectStorageException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ObjectStorageException("Unable to delete object [{$key}].", 0, $e);
        }
    }

    public function exists(string $key): bool
    {
        try {
            return $this->filesystem->exists($key);
        } catch (Throwable $e) {
            throw new ObjectStorageException("Unable to check object [{$key}].", 0, $e);
        }
    }

    public function temporaryUrl(string $key, DateTimeImmutable $expiresAt): string
    {
        try {
            return $this->filesystem->temporaryUrl($key, $expiresAt);
        } catch (Throwable $e) {
            throw new ObjectStorageException("Unable to generate temporary URL for [{$key}].", 0, $e);
        }
    }

    public function metadata(string $key): ObjectMetadata
    {
        try {
            return new ObjectMetadata(
                key: $key,
                size: $this->filesystem->size($key),
                contentType: $this->filesystem->mimeType($key),
                lastModified: (new DateTimeImmutable())->setTimestamp($this->filesystem->lastModified($key)),
            );
        } catch (Throwable $e) {
            throw new ObjectStorageException("Unable to read metadata for [{$key}].", 0, $e);
        }
    }
}
