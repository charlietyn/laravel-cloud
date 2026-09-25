<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Aws;

use Aws\DynamoDb\DynamoDbClient;
use Ronu\LaravelCloud\Contracts\DocumentStore;
use Ronu\LaravelCloud\Exceptions\DocumentStoreException;
use Ronu\LaravelCloud\Support\Json;
use Throwable;

final readonly class DynamoDbDocumentStore implements DocumentStore
{
    /** @param array<string, array{table:string,id_attribute?:string,payload_attribute?:string}> $collections */
    public function __construct(private DynamoDbClient $client, private array $collections) {}

    public function put(string $collection, string $id, array $document): void
    {
        [$table, $idAttribute, $payloadAttribute] = $this->collection($collection);

        try {
            $this->client->putItem([
                'TableName' => $table,
                'Item' => [
                    $idAttribute => ['S' => $id],
                    $payloadAttribute => ['S' => Json::encode($document)],
                ],
            ]);
        } catch (Throwable $e) {
            throw new DocumentStoreException("Unable to store document [{$collection}/{$id}].", 0, $e);
        }
    }

    public function get(string $collection, string $id): ?array
    {
        [$table, $idAttribute, $payloadAttribute] = $this->collection($collection);

        try {
            $result = $this->client->getItem([
                'TableName' => $table,
                'Key' => [$idAttribute => ['S' => $id]],
                'ConsistentRead' => true,
            ]);
            $item = $result->get('Item');
            if (! is_array($item) || ! isset($item[$payloadAttribute]['S'])) {
                return null;
            }

            return Json::decodeObject((string) $item[$payloadAttribute]['S']);
        } catch (Throwable $e) {
            throw new DocumentStoreException("Unable to read document [{$collection}/{$id}].", 0, $e);
        }
    }

    public function delete(string $collection, string $id): void
    {
        [$table, $idAttribute] = $this->collection($collection);

        try {
            $this->client->deleteItem([
                'TableName' => $table,
                'Key' => [$idAttribute => ['S' => $id]],
            ]);
        } catch (Throwable $e) {
            throw new DocumentStoreException("Unable to delete document [{$collection}/{$id}].", 0, $e);
        }
    }

    /** @return array{string,string,string} */
    private function collection(string $alias): array
    {
        $config = $this->collections[$alias] ?? null;
        if ($config === null) {
            throw new DocumentStoreException("Document collection [{$alias}] is not configured.");
        }

        return [
            $config['table'],
            $config['id_attribute'] ?? 'id',
            $config['payload_attribute'] ?? 'payload',
        ];
    }
}
