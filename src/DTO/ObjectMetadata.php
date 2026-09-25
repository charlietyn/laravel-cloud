<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\DTO;

use DateTimeImmutable;

final readonly class ObjectMetadata
{
    public function __construct(
        public string $key,
        public int $size,
        public ?string $contentType,
        public DateTimeImmutable $lastModified,
    ) {}
}
