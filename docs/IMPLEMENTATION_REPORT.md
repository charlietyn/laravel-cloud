# Implementation report

## Architectural choice

A reusable Composer package was selected because no existing Laravel application was supplied and the requirements explicitly target reuse across multiple Laravel projects/apps-generator outputs.

## Implemented requirements

- PHP 8.5 target, strict types, readonly DTOs/classes where useful.
- Separate Domain/Application-facing contracts from AWS infrastructure adapters.
- Central AWS configuration/factory; no adapter reads `env()` directly.
- No Floci-specific service classes or hard-coded Floci endpoint in application code.
- S3 through Laravel Filesystem; SDK clients for SQS/SNS/EventBridge/DynamoDB/Secrets Manager/Lambda.
- Laravel service-provider bindings and package auto-discovery.
- Package-specific exceptions preserve original exceptions as `previous`.
- Fakes for unit testing without cloud infrastructure.
- Floci integration tests for all seven implemented adapters.
- Portable contract tests for S3/SQS/EventBridge/DynamoDB with optional AWS-sandbox execution.
- `cloud:doctor` and guarded/idempotent `cloud:bootstrap`.
- Migration checklist for S3, SQS, SNS, EventBridge, DynamoDB, Lambda and Secrets Manager.

## Intentionally not implemented yet

SES, Parameter Store, CloudWatch and KMS are extension points, not current capabilities. This follows YAGNI: they should receive dedicated contracts only when a real application capability requires them.

## Final checklist

- [x] Domain does not know AWS
- [x] Application does not know Floci
- [x] No `FlociService` classes
- [x] Endpoint configurable
- [x] IAM/default credential chain compatible
- [x] Strict types
- [x] PHP 8.5 target
- [x] Unit tests
- [x] Floci integration tests
- [x] Contract tests
- [x] Controlled package exceptions
- [x] Centralized configuration
- [x] Prepared for real AWS
