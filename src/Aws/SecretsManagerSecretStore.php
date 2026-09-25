<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Aws;

use Aws\SecretsManager\SecretsManagerClient;
use Ronu\LaravelCloud\Contracts\SecretStore;
use Ronu\LaravelCloud\Exceptions\SecretStoreException;
use Throwable;

final readonly class SecretsManagerSecretStore implements SecretStore
{
    /** @param array<string, string> $secrets */
    public function __construct(private SecretsManagerClient $client, private array $secrets = []) {}

    public function get(string $name): string
    {
        try {
            $result = $this->client->getSecretValue(['SecretId' => $this->secrets[$name] ?? $name]);
            $secret = $result->get('SecretString');
            if (! is_string($secret)) {
                throw new SecretStoreException("Secret [{$name}] does not contain a string value.");
            }

            return $secret;
        } catch (SecretStoreException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new SecretStoreException("Unable to read secret [{$name}].", 0, $e);
        }
    }
}
