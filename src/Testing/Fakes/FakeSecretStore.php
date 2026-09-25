<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Testing\Fakes;

use Ronu\LaravelCloud\Contracts\SecretStore;
use Ronu\LaravelCloud\Exceptions\SecretStoreException;

final class FakeSecretStore implements SecretStore
{
    /** @param array<string, string> $secrets */
    public function __construct(private array $secrets = []) {}
    public function get(string $name): string { return $this->secrets[$name] ?? throw new SecretStoreException("Secret [{$name}] not found."); }
    public function set(string $name, string $value): void { $this->secrets[$name] = $value; }
}
