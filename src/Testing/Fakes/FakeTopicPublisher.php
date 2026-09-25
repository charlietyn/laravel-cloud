<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Testing\Fakes;

use Ronu\LaravelCloud\Contracts\TopicPublisher;
use Ronu\LaravelCloud\DTO\MessageIdentifier;
use Ronu\LaravelCloud\DTO\TopicMessage;

final class FakeTopicPublisher implements TopicPublisher
{
    /** @var list<array{topic:string,message:TopicMessage}> */
    public array $published = [];

    public function publish(string $topic, TopicMessage $message): MessageIdentifier
    {
        $this->published[] = ['topic' => $topic, 'message' => $message];
        return new MessageIdentifier(bin2hex(random_bytes(8)));
    }
}
