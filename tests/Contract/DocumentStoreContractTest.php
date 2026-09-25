<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Contract;

use Ronu\LaravelCloud\Contracts\DocumentStore;

final class DocumentStoreContractTest extends ContractTestCase
{
    public function test_document_store_behavior_is_portable(): void
    {
        $store = $this->app->make(DocumentStore::class);
        $id = 'contract-'.bin2hex(random_bytes(6));
        $document = ['value' => 7, 'state' => 'ok'];
        $store->put('default', $id, $document);
        self::assertSame($document, $store->get('default', $id));
        $store->delete('default', $id);
        self::assertNull($store->get('default', $id));
    }
}
