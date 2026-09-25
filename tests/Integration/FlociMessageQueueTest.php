<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Integration;

use Ronu\LaravelCloud\Contracts\MessageQueue;
use Ronu\LaravelCloud\DTO\OutgoingMessage;
use Ronu\LaravelCloud\Tests\FlociIntegrationTestCase;

final class FlociMessageQueueTest extends FlociIntegrationTestCase
{
    public function test_real_sqs_adapter_send_receive_acknowledge_against_floci(): void
    {
        $queue = $this->app->make(MessageQueue::class);
        $queue->send('default', new OutgoingMessage('{"hello":"floci"}', ['type' => 'test']));
        $messages = $queue->receive('default', 1, 1);
        self::assertNotEmpty($messages);
        self::assertSame('{"hello":"floci"}', $messages[0]->body);
        self::assertSame('test', $messages[0]->attributes['type']);
        $queue->acknowledge('default', $messages[0]->receiptHandle);
    }
}
