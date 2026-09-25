<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\DTO;

final readonly class TopicMessage
{
    /** @param array<string, string> $attributes */
    public function __construct(
        public string $body,
        public ?string $subject = null,
        public array $attributes = [],
    ) {}
}
