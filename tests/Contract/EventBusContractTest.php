<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Contract;

use Ronu\LaravelCloud\Contracts\EventBus;
use Ronu\LaravelCloud\DTO\CloudEvent;
use DateTimeImmutable;

final class EventBusContractTest extends ContractTestCase
{
    public function test_event_bus_accepts_the_same_application_event_contract(): void
    {
        $event = new CloudEvent('contract-'.bin2hex(random_bytes(6)), 'contract.test', new DateTimeImmutable(), 1, ['portable' => true], 'laravel-cloud');
        $identifier = $this->app->make(EventBus::class)->publish($event);
        self::assertNotSame('', $identifier->value);
    }
}
