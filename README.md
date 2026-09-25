<div align="center">

# ☁️ Laravel Cloud

### Cloud contracts and AWS adapters for Laravel

Build against **Floci** during local development, MVP and testing, then move to **AWS** without rewriting your business logic.

<br />

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![AWS](https://img.shields.io/badge/AWS-Compatible-FF9900?logo=amazonwebservices&logoColor=white)
![Floci](https://img.shields.io/badge/Floci-Development%20%26%20Testing-4F46E5)
![License](https://img.shields.io/badge/License-MIT-green)

</div>

---

## ✨ Why Laravel Cloud?

`ronu/laravel-cloud` provides a clean boundary between your Laravel application and cloud infrastructure.

Your **Domain** and **Application** layers depend on small, stable contracts — never directly on AWS SDK classes and never on Floci-specific services.

That gives you a simple migration path:

```text
Development / MVP                     Production
─────────────────                     ──────────

Laravel                               Laravel
   │                                     │
   ▼                                     ▼
Cloud Contracts                       Cloud Contracts
   │                                     │
   ▼                                     ▼
AWS Adapters                          AWS Adapters
   │                                     │
   ▼                                     ▼
AWS SDK                               AWS SDK
   │                                     │
   ▼                                     ▼
Floci                                 AWS
```

> **Floci is an endpoint, not an application dependency.**  
> The business code stays the same when you move from Floci to AWS.

---

## 🚀 Features

| Capability | Contract | AWS Adapter | Service |
|---|---|---|---|
| 📦 Object Storage | `ObjectStorage` | `S3ObjectStorage` | Amazon S3 |
| 📨 Message Queue | `MessageQueue` | `SqsMessageQueue` | Amazon SQS |
| ⚡ Event Bus | `EventBus` | `EventBridgeEventBus` | Amazon EventBridge |
| 📡 Pub/Sub | `TopicPublisher` | `SnsTopicPublisher` | Amazon SNS |
| 🔐 Secrets | `SecretStore` | `SecretsManagerSecretStore` | AWS Secrets Manager |
| 🗂️ Document Store | `DocumentStore` | `DynamoDbDocumentStore` | Amazon DynamoDB |
| λ Function Invocation | `FunctionInvoker` | `LambdaFunctionInvoker` | AWS Lambda |

The package also includes:

- Laravel auto-discovery and dependency injection bindings
- Centralized AWS client configuration
- Floci-compatible development configuration
- Test fakes for application/unit tests
- Integration tests against real adapters
- Reusable contract tests for Floci and AWS
- `cloud:doctor` connectivity diagnostics
- `cloud:bootstrap` idempotent local resource provisioning
- Migration documentation for Floci → AWS

---

## 🧱 Architecture

The package follows **Dependency Inversion**, **Hexagonal Architecture** and **Clean Architecture** principles.

```text
┌───────────────────────────────────────────────┐
│             Laravel Application               │
│                                               │
│   Domain / Application                       │
│          │                                    │
│          ▼                                    │
│   Cloud Contracts                            │
│          │                                    │
└──────────┼────────────────────────────────────┘
           │
           ▼
┌───────────────────────────────────────────────┐
│              Laravel Cloud                    │
│                                               │
│  S3ObjectStorage                             │
│  SqsMessageQueue                             │
│  EventBridgeEventBus                         │
│  SnsTopicPublisher                           │
│  SecretsManagerSecretStore                   │
│  DynamoDbDocumentStore                       │
│  LambdaFunctionInvoker                       │
│          │                                    │
│          ▼                                    │
│       AWS SDK                                 │
└──────────┼────────────────────────────────────┘
           │
      ┌────┴────┐
      ▼         ▼
    Floci      AWS
   DEV/TEST    PROD
```

There are intentionally **no** classes such as `FlociS3Service`, `FlociQueueService` or `FlociStorage`.

---

## 📋 Requirements

- PHP **8.5+**
- Laravel / Illuminate **13.x**
- AWS SDK for PHP
- Flysystem AWS S3 adapter
- Docker when using the supplied Floci environment

See [`composer.json`](composer.json) for the exact dependency constraints.

---

## 📦 Installation

### Local path repository

While developing the package locally, add it to your Laravel application's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../laravel-cloud",
      "options": {
        "symlink": true
      }
    }
  ]
}
```

Then install it:

```bash
composer require ronu/laravel-cloud:@dev
php artisan vendor:publish --tag=laravel-cloud-config
```

The service provider is automatically discovered by Laravel.

For the full setup guide, see [Installation](docs/INSTALLATION.md).

---

## ⚡ Quick Start with Floci

Start the provided Floci environment:

```bash
docker compose -f docker-compose.floci.yml up -d
```

Configure your application:

```dotenv
AWS_DEFAULT_REGION=eu-west-2
AWS_ENDPOINT_URL=http://floci:4566

AWS_ACCESS_KEY_ID=test
AWS_SECRET_ACCESS_KEY=test

AWS_BUCKET=myapp-local
AWS_USE_PATH_STYLE_ENDPOINT=true
```

Bootstrap development resources:

```bash
php artisan cloud:bootstrap
```

Verify connectivity:

```bash
php artisan cloud:doctor
```

> Floci should remain on a **private/internal network**. Do not expose its API publicly.

---

## ☁️ AWS Production

In AWS, keep the same adapters and application code.

Change the infrastructure configuration:

```dotenv
AWS_DEFAULT_REGION=eu-west-2
AWS_ENDPOINT_URL=
AWS_BUCKET=myapp-production
AWS_USE_PATH_STYLE_ENDPOINT=false
```

Prefer the standard AWS credential provider chain instead of static credentials:

- IAM Roles
- ECS Task Roles
- EC2 Instance Profiles
- EKS workload identities / IRSA-compatible mechanisms
- Environment credentials only when appropriate

The migration is primarily:

```text
Floci endpoint + development credentials
                  │
                  ▼
AWS endpoints + IAM + production infrastructure
```

Not:

```text
Rewrite business use cases
```

---

## 💡 Usage

Your application depends only on the contract:

```php
<?php

declare(strict_types=1);

use Ronu\LaravelCloud\Contracts\ObjectStorage;

final readonly class UploadPetPhoto
{
    public function __construct(
        private ObjectStorage $storage,
    ) {
    }

    public function execute(
        string $petId,
        string $contents,
    ): void {
        $this->storage->put(
            key: "pets/{$petId}/avatar.jpg",
            contents: $contents,
            contentType: 'image/jpeg',
        );
    }
}
```

The use case does not know whether the object is stored in:

```text
Floci S3 emulator
        or
Amazon S3
```

That decision belongs to infrastructure configuration.

---

## 📨 Laravel Queue vs Message Queue

Laravel Cloud deliberately separates two different concepts:

**Laravel Queue**

Use Laravel's own queue system for Laravel Jobs. If the backend is SQS, configure Laravel's native `sqs` queue driver.

**`MessageQueue`**

Use this contract for infrastructure or service-to-service messaging where your application explicitly produces and consumes messages.

This avoids coupling Laravel Jobs to a generic messaging abstraction unnecessarily.

---

## 🧪 Testing Strategy

Laravel Cloud uses three levels of testing.

### 1. Unit Tests

Application tests use fakes and require neither AWS nor Floci.

```bash
composer test:unit
```

### 2. Adapter Integration Tests

Real AWS adapters run against Floci.

```bash
docker compose -f docker-compose.floci.yml up -d
composer test:integration
```

### 3. Contract Tests

The same behavioral suite can validate Floci and an AWS sandbox.

```bash
CLOUD_CONTRACT_TARGET=floci composer test:contract
```

This is one of the main safeguards against accidental Floci-specific behavior.

---

## 🩺 Artisan Commands

### Cloud Doctor

```bash
php artisan cloud:doctor
```

Checks cloud configuration and connectivity without exposing credentials or secret values.

### Cloud Bootstrap

```bash
php artisan cloud:bootstrap
```

Creates development resources idempotently for local/testing environments.

It is **not** intended to provision production infrastructure.

Use Terraform, CloudFormation, CDK or another infrastructure-as-code solution for production.

---

## 🔄 Floci → AWS Migration

The architectural invariant is simple:

```text
Before
AWS SDK ───────────────► Floci

After
AWS SDK ───────────────► AWS
```

### What changes

- Endpoint configuration
- IAM and credential strategy
- VPC / networking
- Resource names and ARNs
- Production resource provisioning
- Operational monitoring and scaling

### What does not change

- Domain logic
- Application use cases
- Cloud contracts
- AWS adapters
- Business workflows

See the complete [Floci → AWS migration guide](docs/MIGRATION_FLOCI_TO_AWS.md).

---

## 📚 Documentation

| Document | Description |
|---|---|
| [Architecture](docs/ARCHITECTURE.md) | Boundaries, contracts and dependency rules |
| [Installation](docs/INSTALLATION.md) | Laravel integration and Floci setup |
| [Migration](docs/MIGRATION_FLOCI_TO_AWS.md) | Step-by-step Floci → AWS migration |
| [Validation](docs/VALIDATION.md) | Validation and quality checks |
| [Implementation Report](docs/IMPLEMENTATION_REPORT.md) | Current implementation summary |

---

## 🛡️ Design Principles

- **Dependency Inversion** — application code depends on contracts.
- **No Floci coupling** — Floci exists only as configurable infrastructure.
- **No AWS SDK leakage** — SDK objects and exceptions stay outside the domain.
- **Small interfaces** — capabilities are separated instead of using a god `CloudService`.
- **Testability** — every adapter can be replaced by a fake.
- **Portability** — the same application code runs against Floci or AWS.
- **Production IAM support** — static credentials are not required.
- **YAGNI** — only useful cloud capabilities are abstracted.

---

## 🔒 Security

Never commit real cloud credentials or secrets.

Floci is intended to be used as internal development/testing infrastructure and should not be exposed directly to the public Internet.

Production workloads should use AWS IAM and appropriately provisioned infrastructure.

---

## 🗺️ Project Philosophy

```text
Develop cheaply.
Test realistically.
Keep the domain clean.
Scale when the product needs it.
Move to AWS without rewriting the business.
```

---

## 📄 License

Laravel Cloud is open-sourced software licensed under the [MIT license](LICENSE).

---

<div align="center">

**Laravel Cloud**

Clean cloud boundaries for Laravel — **Floci today, AWS tomorrow.**

</div>
