<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Support;

use InvalidArgumentException;

final readonly class AwsClientConfiguration
{
    public function __construct(
        public string $region,
        public ?string $endpoint,
        public ?string $accessKey,
        public ?string $secretKey,
        public ?string $sessionToken,
        public float $connectTimeoutSeconds,
        public float $timeoutSeconds,
        public int $retries,
        public bool $s3PathStyle,
    ) {
        if (($accessKey === null) !== ($secretKey === null)) {
            throw new InvalidArgumentException('AWS access key and secret key must either both be configured or both be omitted.');
        }

        if ($retries < 0) {
            throw new InvalidArgumentException('AWS retries cannot be negative.');
        }
    }

    /** @param array<string, mixed> $config */
    public static function fromArray(array $config): self
    {
        $credentials = is_array($config['credentials'] ?? null) ? $config['credentials'] : [];
        $http = is_array($config['http'] ?? null) ? $config['http'] : [];
        $s3 = is_array($config['s3'] ?? null) ? $config['s3'] : [];

        return new self(
            region: (string) ($config['region'] ?? 'eu-west-2'),
            endpoint: self::nullableString($config['endpoint'] ?? null),
            accessKey: self::nullableString($credentials['key'] ?? null),
            secretKey: self::nullableString($credentials['secret'] ?? null),
            sessionToken: self::nullableString($credentials['token'] ?? null),
            connectTimeoutSeconds: (float) ($http['connect_timeout_seconds'] ?? 2.0),
            timeoutSeconds: (float) ($http['timeout_seconds'] ?? 10.0),
            retries: (int) ($http['retries'] ?? 3),
            s3PathStyle: (bool) ($s3['path_style'] ?? false),
        );
    }

    /** @return array{key:string,secret:string,token?:string}|null */
    public function credentials(): ?array
    {
        if ($this->accessKey === null || $this->secretKey === null) {
            return null;
        }

        $credentials = ['key' => $this->accessKey, 'secret' => $this->secretKey];
        if ($this->sessionToken !== null) {
            $credentials['token'] = $this->sessionToken;
        }

        return $credentials;
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
