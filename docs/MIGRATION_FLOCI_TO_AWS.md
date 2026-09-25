# Migration: Floci -> AWS

The application layer does not change. Migration is infrastructure/configuration work.

## Global

Floci/MVP:

```dotenv
AWS_ENDPOINT_URL=http://floci:4566
AWS_ACCESS_KEY_ID=test
AWS_SECRET_ACCESS_KEY=test
AWS_USE_PATH_STYLE_ENDPOINT=true
```

AWS:

```dotenv
AWS_ENDPOINT_URL=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

Prefer IAM roles/task roles/instance profiles/IRSA to static production credentials.

## S3

- Provision the production bucket and policies.
- Copy existing objects from the Floci-backed environment if they contain MVP data that must be retained.
- Configure bucket name, CORS, lifecycle, encryption and public-access block.
- Validate presigned URL behavior with the contract suite.

## SQS

- Provision queues and DLQs.
- Map logical queue aliases to production queue names or URLs.
- Configure visibility timeout and redrive policy for actual workloads.
- Make consumers idempotent; do not assume single delivery.

## SNS

- Provision topics/subscriptions.
- Map logical aliases to topic ARN/name.
- Validate subscription delivery and retry policies outside this package.

## EventBridge

- Provision event buses, rules and targets.
- Keep `event_type`, `source`, event envelope and version stable.
- Validate target-side failures because PutEvents acceptance is not equivalent to target delivery.

## DynamoDB

- Provision tables, encryption, PITR, backups and capacity mode.
- Export/import MVP documents if needed.
- Keep repository/document contracts stable while infrastructure changes underneath.

## Lambda

- Build/deploy functions with AWS-supported runtimes, roles and networking.
- Map logical function aliases to production function names/ARNs.
- Validate timeout, concurrency, retries, DLQs/destinations and payload size.

## Secrets Manager

- Create production secrets independently; never copy dummy/local secrets blindly.
- Give the application role least-privilege read access.
- Prefer loading stable configuration during bootstrap where practical instead of querying Secrets Manager in every business operation.

## Contract verification

Run the same suite against Floci:

```bash
CLOUD_CONTRACT_TARGET=floci composer test:contract
```

Then against an isolated AWS sandbox with IAM credentials provided by the standard credential chain:

```bash
CLOUD_CONTRACT_TARGET=aws \
CLOUD_CONTRACT_ALLOW_AWS=true \
CLOUD_CONTRACT_S3_BUCKET=your-sandbox-bucket \
CLOUD_CONTRACT_SQS_QUEUE=your-sandbox-queue \
CLOUD_CONTRACT_EVENT_BUS=your-sandbox-bus \
CLOUD_CONTRACT_DYNAMODB_TABLE=your-sandbox-table \
composer test:contract
```

The AWS resources must already exist; `cloud:bootstrap` never provisions production infrastructure automatically.
