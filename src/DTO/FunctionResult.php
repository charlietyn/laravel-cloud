<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\DTO;

final readonly class FunctionResult
{
    /** @param array<string, mixed>|null $payload */
    public function __construct(
        public int $statusCode,
        public ?array $payload,
        public bool $asynchronous,
    ) {}
}
