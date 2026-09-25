<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Contracts;

use Ronu\LaravelCloud\DTO\MessageIdentifier;
use Ronu\LaravelCloud\DTO\OutgoingMessage;
use Ronu\LaravelCloud\DTO\ReceivedMessage;

interface MessageQueue
{
    public function send(string $queue, OutgoingMessage $message): MessageIdentifier;

    /** @return list<ReceivedMessage> */
    public function receive(string $queue, int $maxMessages = 1, int $waitSeconds = 0): array;

    public function acknowledge(string $queue, string $receiptHandle): void;
}
