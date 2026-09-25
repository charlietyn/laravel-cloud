<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Contracts;

use Ronu\LaravelCloud\DTO\MessageIdentifier;
use Ronu\LaravelCloud\DTO\TopicMessage;

interface TopicPublisher
{
    public function publish(string $topic, TopicMessage $message): MessageIdentifier;
}
