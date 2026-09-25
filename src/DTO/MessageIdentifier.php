<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\DTO;

final readonly class MessageIdentifier
{
    public function __construct(public string $value) {}
}
