<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Testing\Fakes;

use Ronu\LaravelCloud\Contracts\FunctionInvoker;
use Ronu\LaravelCloud\DTO\FunctionInvocation;
use Ronu\LaravelCloud\DTO\FunctionResult;

final class FakeFunctionInvoker implements FunctionInvoker
{
    /** @var list<array{function:string,invocation:FunctionInvocation}> */
    public array $invocations = [];

    /** @param array<string, mixed>|null $payload */
    public function __construct(private readonly ?array $payload = ['ok' => true]) {}

    public function invoke(string $function, FunctionInvocation $invocation): FunctionResult
    {
        $this->invocations[] = ['function' => $function, 'invocation' => $invocation];
        return new FunctionResult(200, $this->payload, false);
    }
}
