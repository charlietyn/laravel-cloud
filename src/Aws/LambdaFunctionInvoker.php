<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Aws;

use Aws\Lambda\LambdaClient;
use Ronu\LaravelCloud\Contracts\FunctionInvoker;
use Ronu\LaravelCloud\DTO\FunctionInvocation;
use Ronu\LaravelCloud\DTO\FunctionInvocationMode;
use Ronu\LaravelCloud\DTO\FunctionResult;
use Ronu\LaravelCloud\Exceptions\FunctionInvocationException;
use Ronu\LaravelCloud\Support\Json;
use Throwable;

final readonly class LambdaFunctionInvoker implements FunctionInvoker
{
    /** @param array<string, string> $functions */
    public function __construct(private LambdaClient $client, private array $functions = []) {}

    public function invoke(string $function, FunctionInvocation $invocation): FunctionResult
    {
        $name = $this->functions[$function] ?? $function;
        $async = $invocation->mode === FunctionInvocationMode::Asynchronous;

        try {
            $result = $this->client->invoke([
                'FunctionName' => $name,
                'InvocationType' => $async ? 'Event' : 'RequestResponse',
                'Payload' => Json::encode($invocation->payload),
            ]);

            if ($result->get('FunctionError') !== null) {
                throw new FunctionInvocationException("Function [{$function}] returned an execution error.");
            }

            $payload = null;
            if (! $async) {
                $raw = $result->get('Payload');
                $contents = is_object($raw) && method_exists($raw, 'getContents') ? $raw->getContents() : (string) $raw;
                if ($contents !== '') {
                    $payload = Json::decodeObject($contents);
                }
            }

            return new FunctionResult(
                statusCode: (int) ($result->get('@metadata')['statusCode'] ?? ($async ? 202 : 200)),
                payload: $payload,
                asynchronous: $async,
            );
        } catch (FunctionInvocationException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new FunctionInvocationException("Unable to invoke function [{$function}].", 0, $e);
        }
    }
}
