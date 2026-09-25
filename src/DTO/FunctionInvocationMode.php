<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\DTO;

enum FunctionInvocationMode: string
{
    case Synchronous = 'synchronous';
    case Asynchronous = 'asynchronous';
}
