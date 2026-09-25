<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Integration;

use Ronu\LaravelCloud\Contracts\TopicPublisher;
use Ronu\LaravelCloud\DTO\TopicMessage;
use Ronu\LaravelCloud\Tests\FlociIntegrationTestCase;

final class FlociTopicPublisherTest extends FlociIntegrationTestCase
{
    public function test_real_sns_adapter_publishes_against_floci(): void
    {
        $id = $this->app->make(TopicPublisher::class)->publish('default', new TopicMessage('hello', 'integration'));
        self::assertNotSame('', $id->value);
    }
}
