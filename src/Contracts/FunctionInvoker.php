<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Contracts;

use Ronu\LaravelCloud\DTO\FunctionInvocation;
use Ronu\LaravelCloud\DTO\FunctionResult;

interface FunctionInvoker
{
    public function invoke(string $function, FunctionInvocation $invocation): FunctionResult;
}
