<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Contracts;

interface SecretStore
{
    public function get(string $name): string;
}
