# Architecture

## Decision

This repository is a reusable Composer package rather than an application-internal module. No host Laravel project was supplied, and the requested use case is reuse across multiple generated Laravel applications.

```text
Domain / Application
       |
       v
Cloud Contracts
       |
       v
AWS Adapters
       |
       v
AWS SDK / Laravel Filesystem
       |
   +---+---+
   |       |
 Floci    AWS
 DEV/MVP  PROD
```

## Boundaries

- Domain and Application import only `Contracts` and DTOs.
- No class named `Floci*` exists. Floci is configuration only.
- S3 uses Laravel's filesystem abstraction for application-level object operations.
- SQS infrastructure messaging remains separate from Laravel Queue Jobs.
- SNS and EventBridge are separate contracts because pub/sub topics and event routing have different semantics.
- DynamoDB is exposed as a deliberately small document capability, not a universal database abstraction.
- AWS SDK exceptions are translated to package exceptions and retained as `previous` exceptions.
- `env()` exists only in `config/cloud.php`; runtime classes receive configuration through Laravel's config repository and DI container.

## Resource aliases

Application code uses logical names such as `default`, `billing`, or `notifications`. Configuration maps those names to queue names/URLs, topic names/ARNs, bus names, tables, secrets, and functions. ARN and endpoint details remain in Infrastructure.

## Event envelope

```json
{
  "event_id": "...",
  "event_type": "appointment.created",
  "occurred_at": "2026-09-25T10:00:00+01:00",
  "version": 1,
  "data": {}
}
```

Consumers should use `event_id` for idempotency and `version` for schema evolution.

## Resilience

The AWS SDK remains responsible for transport retries/backoff. The package exposes timeout/retry configuration but does not add a second retry loop. Consumers must treat SQS delivery as at-least-once, make handlers idempotent, and configure DLQs in infrastructure-as-code when required.

## Observability

Adapters deliberately do not log secrets, access keys, payload secrets, or tokens. Production applications should wrap the contracts with decorators/middleware if they need metrics for operation, service, resource alias, latency, request/message/event ID and failures.
