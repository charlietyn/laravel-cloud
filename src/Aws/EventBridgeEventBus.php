<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Aws;

use Aws\EventBridge\EventBridgeClient;
use Ronu\LaravelCloud\Contracts\EventBus;
use Ronu\LaravelCloud\DTO\CloudEvent;
use Ronu\LaravelCloud\DTO\EventIdentifier;
use Ronu\LaravelCloud\Exceptions\EventPublishingException;
use Ronu\LaravelCloud\Support\Json;
use Throwable;

final readonly class EventBridgeEventBus implements EventBus
{
    /** @param array<string, string> $buses */
    public function __construct(private EventBridgeClient $client, private array $buses = []) {}

    public function publish(CloudEvent $event, string $bus = 'default'): EventIdentifier
    {
        try {
            $result = $this->client->putEvents([
                'Entries' => [[
                    'EventBusName' => $this->buses[$bus] ?? $bus,
                    'Source' => $event->source,
                    'DetailType' => $event->type,
                    'Detail' => Json::encode($event->envelope()),
                    'Time' => $event->occurredAt,
                ]],
            ]);

            if ((int) ($result->get('FailedEntryCount') ?? 0) > 0) {
                $entry = ($result->get('Entries') ?? [])[0] ?? [];
                throw new EventPublishingException((string) ($entry['ErrorMessage'] ?? 'EventBridge rejected the event.'));
            }

            $id = (string) ((($result->get('Entries') ?? [])[0]['EventId'] ?? ''));

            return new EventIdentifier($id !== '' ? $id : $event->id);
        } catch (EventPublishingException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new EventPublishingException("Unable to publish event [{$event->type}].", 0, $e);
        }
    }
}
