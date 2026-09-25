<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\DTO;

final readonly class ReceivedMessage
{
    /** @param array<string, string> $attributes */
    public function __construct(
        public string $id,
        public string $body,
        public array $attributes,
        public string $receiptHandle,
    ) {}
}
