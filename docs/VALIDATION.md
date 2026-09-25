# Validation report

Generated: 2026-09-25

## Automated checks executed in the generation environment

- PHP syntax lint: PASS for all 65 PHP files under `src/`, `tests/`, `config/`, and `examples/`.
- `composer.json` JSON parse: PASS.
- Architecture grep: PASS — no Floci-specific service class names or hard-coded `http://floci:4566` in `src/`.
- Configuration boundary grep: PASS — runtime `src/` does not read environment variables directly.
- Dependency boundary grep: PASS — Contracts and DTOs do not import AWS SDK classes.
- Strict types: PASS for all PHP files.

## Environment limitations

The generation runtime provides PHP 8.4.23, while this package deliberately targets PHP 8.5 as requested. The code uses stable language features available before 8.5 and avoids speculative PHP 8.5-only syntax.

Composer and Docker are not installed in the generation runtime and outbound package downloads are unavailable, so dependency installation, PHPUnit execution, and live Floci integration execution could not be run here. The repository includes the commands and integration/contract suites required to run those checks in a PHP 8.5 + Docker environment.

## Recommended verification on the target workstation / CI

```bash
composer install
composer validate --strict
composer test:unit

docker compose -f docker-compose.floci.yml up -d
composer test:integration
CLOUD_CONTRACT_TARGET=floci composer test:contract

php artisan cloud:doctor
```
