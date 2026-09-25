<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Testing\Fakes;

use Ronu\LaravelCloud\Contracts\MessageQueue;
use Ronu\LaravelCloud\DTO\MessageIdentifier;
use Ronu\LaravelCloud\DTO\OutgoingMessage;
use Ronu\LaravelCloud\DTO\ReceivedMessage;

final class FakeMessageQueue implements MessageQueue
{
    /** @var array<string, list<ReceivedMessage>> */
    private array $messages = [];

    public function send(string $queue, OutgoingMessage $message): MessageIdentifier
    {
        $id = bin2hex(random_bytes(8));
        $this->messages[$queue][] = new ReceivedMessage($id, $message->body, $message->attributes, 'fake-'.$id);
        return new MessageIdentifier($id);
    }

    public function receive(string $queue, int $maxMessages = 1, int $waitSeconds = 0): array
    {
        return array_slice($this->messages[$queue] ?? [], 0, $maxMessages);
    }

    public function acknowledge(string $queue, string $receiptHandle): void
    {
        $this->messages[$queue] = array_values(array_filter(
            $this->messages[$queue] ?? [],
            static fn (ReceivedMessage $message): bool => $message->receiptHandle !== $receiptHandle,
        ));
    }
}
