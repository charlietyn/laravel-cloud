<?php

declare(strict_types=1);

use Ronu\LaravelCloud\Contracts\EventBus;
use Ronu\LaravelCloud\DTO\CloudEvent;
use DateTimeImmutable;

final readonly class PublishAppointmentCreated
{
    public function __construct(private EventBus $events) {}

    public function execute(string $eventId, string $appointmentId, string $petId): void
    {
        $this->events->publish(new CloudEvent(
            id: $eventId,
            type: 'appointment.created',
            occurredAt: new DateTimeImmutable(),
            version: 1,
            data: ['appointment_id' => $appointmentId, 'pet_id' => $petId],
            source: 'kwikvet.appointments',
        ));
    }
}
