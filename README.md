# ronu/laravel-cloud

Reusable PHP 8.5 / Laravel 13 cloud boundaries for applications that run against **Floci during development/MVP/testing** and migrate to **AWS without rewriting business use cases**.

## What is implemented

| Capability | Contract | AWS adapter |
|---|---|---|
| Object storage | `ObjectStorage` | `S3ObjectStorage` |
| Infrastructure queue | `MessageQueue` | `SqsMessageQueue` |
| Event routing | `EventBus` | `EventBridgeEventBus` |
| Pub/Sub topic | `TopicPublisher` | `SnsTopicPublisher` |
| Secrets | `SecretStore` | `SecretsManagerSecretStore` |
| Documents | `DocumentStore` | `DynamoDbDocumentStore` |
| Functions | `FunctionInvoker` | `LambdaFunctionInvoker` |

There are deliberately **no Floci-specific application classes**. Floci is only an AWS-compatible endpoint configured through environment/configuration.

## Architecture

```text
Application / Domain
        |
        v
Cloud contracts + package DTOs
        |
        v
AWS adapters
        |
        +--> Laravel Filesystem / AWS SDK
                    |
               +----+----+
               |         |
             Floci      AWS
           DEV / MVP    PROD
```

## Install

```bash
composer require ronu/laravel-cloud
php artisan vendor:publish --tag=laravel-cloud-config
```

For development, start Floci:

```bash
docker compose -f docker-compose.floci.yml up -d
php artisan cloud:bootstrap
php artisan cloud:doctor
```

## Floci configuration

```dotenv
AWS_DEFAULT_REGION=eu-west-2
AWS_ENDPOINT_URL=http://floci:4566
AWS_ACCESS_KEY_ID=test
AWS_SECRET_ACCESS_KEY=test
AWS_BUCKET=myapp-local
AWS_USE_PATH_STYLE_ENDPOINT=true
```

## AWS production configuration

```dotenv
AWS_DEFAULT_REGION=eu-west-2
AWS_ENDPOINT_URL=
AWS_BUCKET=myapp-production
AWS_USE_PATH_STYLE_ENDPOINT=false
```

Do not set static credentials when the workload has an IAM role. The AWS SDK will use its normal credential chain.

## Example: application use case

```php
use Ronu\LaravelCloud\Contracts\ObjectStorage;

final readonly class UploadPetPhoto
{
    public function __construct(private ObjectStorage $storage) {}

    public function execute(string $petId, string $contents): void
    {
        $this->storage->put(
            key: "pets/{$petId}/avatar.jpg",
            contents: $contents,
            contentType: 'image/jpeg',
        );
    }
}
```

The use case knows nothing about S3, AWS SDK or Floci.

## Laravel Queue vs MessageQueue

`MessageQueue` is for service-to-service/infrastructure messaging. Laravel Jobs should continue to use Laravel Queue. If Laravel Jobs use SQS, configure Laravel's `sqs` queue driver; do not route Laravel Jobs through this package merely because SQS exists.

## Tests

Unit tests do not need AWS or Floci:

```bash
composer test:unit
```

Adapter integration tests use real adapters against Floci:

```bash
docker compose -f docker-compose.floci.yml up -d
composer test:integration
```

Contract tests run the same behavior against Floci and, optionally, an AWS sandbox:

```bash
CLOUD_CONTRACT_TARGET=floci composer test:contract
```

See `docs/MIGRATION_FLOCI_TO_AWS.md` for AWS sandbox execution.

## Artisan commands

```bash
php artisan cloud:doctor
php artisan cloud:bootstrap
```

`cloud:doctor` checks connectivity without printing credentials or secret values. `cloud:bootstrap` is guarded for local/testing usage and creates development resources idempotently; it is not a production provisioning system.

## Migration invariant

The desired migration is:

```text
Before: AWS SDK -> http://floci:4566
After:  AWS SDK -> AWS default endpoints + IAM
```

Business code remains unchanged. What changes: endpoint, IAM, networking, resource names/ARNs and infrastructure provisioning.

More detail: `docs/ARCHITECTURE.md`, `docs/INSTALLATION.md`, `docs/MIGRATION_FLOCI_TO_AWS.md`.
