<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Contracts;

interface DocumentStore
{
    /** @param array<string, mixed> $document */
    public function put(string $collection, string $id, array $document): void;

    /** @return array<string, mixed>|null */
    public function get(string $collection, string $id): ?array;

    public function delete(string $collection, string $id): void;
}
