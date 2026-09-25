<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Unit;

use Ronu\LaravelCloud\DTO\CloudEvent;
use Ronu\LaravelCloud\Tests\TestCase;
use DateTimeImmutable;

final class CloudEventTest extends TestCase
{
    public function test_it_builds_a_stable_versioned_envelope(): void
    {
        $event = new CloudEvent('evt-1', 'appointment.created', new DateTimeImmutable('2026-09-25T10:00:00+01:00'), 1, ['appointment_id' => 'a-1']);
        self::assertSame('evt-1', $event->envelope()['event_id']);
        self::assertSame('appointment.created', $event->envelope()['event_type']);
        self::assertSame(1, $event->envelope()['version']);
        self::assertSame(['appointment_id' => 'a-1'], $event->envelope()['data']);
    }
}
