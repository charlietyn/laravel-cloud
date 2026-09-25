<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Integration;

use Ronu\LaravelCloud\Contracts\EventBus;
use Ronu\LaravelCloud\DTO\CloudEvent;
use Ronu\LaravelCloud\Tests\FlociIntegrationTestCase;
use DateTimeImmutable;

final class FlociEventBusTest extends FlociIntegrationTestCase
{
    public function test_real_eventbridge_adapter_publishes_against_floci(): void
    {
        $id = $this->app->make(EventBus::class)->publish(new CloudEvent(
            id: 'evt-'.bin2hex(random_bytes(4)),
            type: 'test.created',
            occurredAt: new DateTimeImmutable(),
            version: 1,
            data: ['ok' => true],
            source: 'laravel-cloud-tests',
        ));
        self::assertNotSame('', $id->value);
    }
}
