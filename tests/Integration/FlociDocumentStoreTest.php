<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Integration;

use Ronu\LaravelCloud\Contracts\DocumentStore;
use Ronu\LaravelCloud\Tests\FlociIntegrationTestCase;

final class FlociDocumentStoreTest extends FlociIntegrationTestCase
{
    public function test_real_dynamodb_adapter_round_trip_against_floci(): void
    {
        $store = $this->app->make(DocumentStore::class);
        $id = 'doc-'.bin2hex(random_bytes(4));
        $store->put('default', $id, ['state' => 'ready', 'version' => 1]);
        self::assertSame(['state' => 'ready', 'version' => 1], $store->get('default', $id));
        $store->delete('default', $id);
        self::assertNull($store->get('default', $id));
    }
}
