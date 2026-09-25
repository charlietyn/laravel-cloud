<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Contract;

use Ronu\LaravelCloud\Contracts\MessageQueue;
use Ronu\LaravelCloud\DTO\OutgoingMessage;

final class MessageQueueContractTest extends ContractTestCase
{
    public function test_message_queue_behavior_is_portable(): void
    {
        $queue = $this->app->make(MessageQueue::class);
        $marker = bin2hex(random_bytes(8));
        $queue->send('default', new OutgoingMessage($marker, ['contract' => 'v1']));
        $messages = $queue->receive('default', 10, 1);
        $match = null;
        foreach ($messages as $message) {
            if ($message->body === $marker) { $match = $message; break; }
        }
        self::assertNotNull($match);
        self::assertSame('v1', $match->attributes['contract'] ?? null);
        $queue->acknowledge('default', $match->receiptHandle);
    }
}
