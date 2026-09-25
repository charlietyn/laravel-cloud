<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Aws;

use Aws\Sns\SnsClient;
use Ronu\LaravelCloud\Contracts\TopicPublisher;
use Ronu\LaravelCloud\DTO\MessageIdentifier;
use Ronu\LaravelCloud\DTO\TopicMessage;
use Ronu\LaravelCloud\Exceptions\TopicPublishingException;
use Throwable;

final class SnsTopicPublisher implements TopicPublisher
{
    /** @var array<string, string> */
    private array $arnCache = [];

    /** @param array<string, string> $topics */
    public function __construct(private readonly SnsClient $client, private readonly array $topics = []) {}

    public function publish(string $topic, TopicMessage $message): MessageIdentifier
    {
        try {
            $payload = ['TopicArn' => $this->topicArn($topic), 'Message' => $message->body];
            if ($message->subject !== null) {
                $payload['Subject'] = $message->subject;
            }
            if ($message->attributes !== []) {
                $payload['MessageAttributes'] = [];
                foreach ($message->attributes as $name => $value) {
                    $payload['MessageAttributes'][$name] = ['DataType' => 'String', 'StringValue' => $value];
                }
            }

            $result = $this->client->publish($payload);

            return new MessageIdentifier((string) $result->get('MessageId'));
        } catch (Throwable $e) {
            throw new TopicPublishingException("Unable to publish to topic [{$topic}].", 0, $e);
        }
    }

    private function topicArn(string $alias): string
    {
        if (isset($this->arnCache[$alias])) {
            return $this->arnCache[$alias];
        }

        $topic = $this->topics[$alias] ?? $alias;
        if (str_starts_with($topic, 'arn:')) {
            return $this->arnCache[$alias] = $topic;
        }

        $nextToken = null;
        do {
            $args = $nextToken === null ? [] : ['NextToken' => $nextToken];
            $result = $this->client->listTopics($args);
            foreach ($result->get('Topics') ?? [] as $candidate) {
                $arn = (string) ($candidate['TopicArn'] ?? '');
                if ($arn !== '' && str_ends_with($arn, ':'.$topic)) {
                    return $this->arnCache[$alias] = $arn;
                }
            }
            $nextToken = $result->get('NextToken');
        } while (is_string($nextToken) && $nextToken !== '');

        throw new TopicPublishingException("Topic [{$alias}] is not provisioned. Run cloud:bootstrap locally or configure its ARN/name.");
    }
}
