<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\DTO;

use InvalidArgumentException;

final readonly class OutgoingMessage
{
    /** @param array<string, string> $attributes */
    public function __construct(
        public string $body,
        public array $attributes = [],
        public ?int $delaySeconds = null,
    ) {
        if ($delaySeconds !== null && ($delaySeconds < 0 || $delaySeconds > 900)) {
            throw new InvalidArgumentException('delaySeconds must be between 0 and 900 seconds.');
        }
    }
}
