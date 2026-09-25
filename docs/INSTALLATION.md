# Installation and integration

## Local path repository

In a Laravel application:

```json
{
  "repositories": [
    {"type": "path", "url": "../ronu-laravel-cloud", "options": {"symlink": true}}
  ]
}
```

Then:

```bash
composer require ronu/laravel-cloud:@dev
php artisan vendor:publish --tag=laravel-cloud-config
```

The service provider is auto-discovered.

## Floci

```bash
docker compose -f docker-compose.floci.yml up -d
cp .env.testing.example .env.testing
php artisan cloud:bootstrap
php artisan cloud:doctor
```

The supplied Compose file binds port 4566 only to host loopback. Do not expose Floci publicly.

## Production AWS

Remove `AWS_ENDPOINT_URL`, disable path-style S3 unless specifically required, and use the standard AWS credential provider chain. Provision resources with Terraform/CloudFormation/CDK rather than `cloud:bootstrap`.
