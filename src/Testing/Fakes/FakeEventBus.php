<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Testing\Fakes;

use Ronu\LaravelCloud\Contracts\EventBus;
use Ronu\LaravelCloud\DTO\CloudEvent;
use Ronu\LaravelCloud\DTO\EventIdentifier;

final class FakeEventBus implements EventBus
{
    /** @var list<array{bus:string,event:CloudEvent}> */
    public array $published = [];

    public function publish(CloudEvent $event, string $bus = 'default'): EventIdentifier
    {
        $this->published[] = ['bus' => $bus, 'event' => $event];
        return new EventIdentifier($event->id);
    }
}
