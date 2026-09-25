<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Contracts;

use Ronu\LaravelCloud\DTO\CloudEvent;
use Ronu\LaravelCloud\DTO\EventIdentifier;

interface EventBus
{
    public function publish(CloudEvent $event, string $bus = 'default'): EventIdentifier;
}
