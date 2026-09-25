<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Contracts;

use Ronu\LaravelCloud\DTO\ObjectMetadata;
use DateTimeImmutable;

interface ObjectStorage
{
    /** @param array<string, string> $metadata */
    public function put(string $key, string $contents, ?string $contentType = null, array $metadata = []): void;

    public function get(string $key): string;

    public function delete(string $key): void;

    public function exists(string $key): bool;

    public function temporaryUrl(string $key, DateTimeImmutable $expiresAt): string;

    public function metadata(string $key): ObjectMetadata;
}
