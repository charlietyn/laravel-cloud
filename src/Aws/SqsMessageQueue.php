<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Aws;

use Aws\Sqs\SqsClient;
use Ronu\LaravelCloud\Contracts\MessageQueue;
use Ronu\LaravelCloud\DTO\MessageIdentifier;
use Ronu\LaravelCloud\DTO\OutgoingMessage;
use Ronu\LaravelCloud\DTO\ReceivedMessage;
use Ronu\LaravelCloud\Exceptions\MessageQueueException;
use InvalidArgumentException;
use Throwable;

final class SqsMessageQueue implements MessageQueue
{
    /** @var array<string, string> */
    private array $urlCache = [];

    /** @param array<string, string> $queues */
    public function __construct(private readonly SqsClient $client, private readonly array $queues = []) {}

    public function send(string $queue, OutgoingMessage $message): MessageIdentifier
    {
        try {
            $payload = [
                'QueueUrl' => $this->queueUrl($queue),
                'MessageBody' => $message->body,
            ];

            if ($message->delaySeconds !== null) {
                $payload['DelaySeconds'] = $message->delaySeconds;
            }

            if ($message->attributes !== []) {
                $payload['MessageAttributes'] = [];
                foreach ($message->attributes as $name => $value) {
                    $payload['MessageAttributes'][$name] = ['DataType' => 'String', 'StringValue' => $value];
                }
            }

            $result = $this->client->sendMessage($payload);
            $id = (string) $result->get('MessageId');

            return new MessageIdentifier($id);
        } catch (Throwable $e) {
            throw new MessageQueueException("Unable to send message to queue [{$queue}].", 0, $e);
        }
    }

    public function receive(string $queue, int $maxMessages = 1, int $waitSeconds = 0): array
    {
        if ($maxMessages < 1 || $maxMessages > 10) {
            throw new InvalidArgumentException('maxMessages must be between 1 and 10.');
        }
        if ($waitSeconds < 0 || $waitSeconds > 20) {
            throw new InvalidArgumentException('waitSeconds must be between 0 and 20.');
        }

        try {
            $result = $this->client->receiveMessage([
                'QueueUrl' => $this->queueUrl($queue),
                'MaxNumberOfMessages' => $maxMessages,
                'WaitTimeSeconds' => $waitSeconds,
                'MessageAttributeNames' => ['All'],
            ]);

            $messages = [];
            foreach ($result->get('Messages') ?? [] as $message) {
                $attributes = [];
                foreach ($message['MessageAttributes'] ?? [] as $name => $attribute) {
                    if (isset($attribute['StringValue'])) {
                        $attributes[(string) $name] = (string) $attribute['StringValue'];
                    }
                }

                $messages[] = new ReceivedMessage(
                    id: (string) ($message['MessageId'] ?? ''),
                    body: (string) ($message['Body'] ?? ''),
                    attributes: $attributes,
                    receiptHandle: (string) ($message['ReceiptHandle'] ?? ''),
                );
            }

            return $messages;
        } catch (Throwable $e) {
            throw new MessageQueueException("Unable to receive messages from queue [{$queue}].", 0, $e);
        }
    }

    public function acknowledge(string $queue, string $receiptHandle): void
    {
        try {
            $this->client->deleteMessage([
                'QueueUrl' => $this->queueUrl($queue),
                'ReceiptHandle' => $receiptHandle,
            ]);
        } catch (Throwable $e) {
            throw new MessageQueueException("Unable to acknowledge message from queue [{$queue}].", 0, $e);
        }
    }

    private function queueUrl(string $alias): string
    {
        if (isset($this->urlCache[$alias])) {
            return $this->urlCache[$alias];
        }

        $queue = $this->queues[$alias] ?? $alias;
        if (str_starts_with($queue, 'http://') || str_starts_with($queue, 'https://')) {
            return $this->urlCache[$alias] = $queue;
        }

        $result = $this->client->getQueueUrl(['QueueName' => $queue]);
        $url = (string) $result->get('QueueUrl');
        if ($url === '') {
            throw new MessageQueueException("Queue [{$alias}] resolved without a URL.");
        }

        return $this->urlCache[$alias] = $url;
    }
}
