<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\DTO;

use DateTimeImmutable;

final readonly class CloudEvent
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public string $id,
        public string $type,
        public DateTimeImmutable $occurredAt,
        public int $version,
        public array $data,
        public string $source = 'app',
    ) {}

    /** @return array{event_id:string,event_type:string,occurred_at:string,version:int,data:array<string,mixed>} */
    public function envelope(): array
    {
        return [
            'event_id' => $this->id,
            'event_type' => $this->type,
            'occurred_at' => $this->occurredAt->format(DATE_ATOM),
            'version' => $this->version,
            'data' => $this->data,
        ];
    }
}
