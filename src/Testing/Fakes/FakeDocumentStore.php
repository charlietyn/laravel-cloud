<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Testing\Fakes;

use Ronu\LaravelCloud\Contracts\DocumentStore;

final class FakeDocumentStore implements DocumentStore
{
    /** @var array<string, array<string, array<string, mixed>>> */
    private array $documents = [];
    public function put(string $collection, string $id, array $document): void { $this->documents[$collection][$id] = $document; }
    public function get(string $collection, string $id): ?array { return $this->documents[$collection][$id] ?? null; }
    public function delete(string $collection, string $id): void { unset($this->documents[$collection][$id]); }
}
