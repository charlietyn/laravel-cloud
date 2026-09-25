<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\DTO;

final readonly class FunctionInvocation
{
    /** @param array<string, mixed> $payload */
    public function __construct(
        public array $payload,
        public FunctionInvocationMode $mode = FunctionInvocationMode::Synchronous,
    ) {}
}
