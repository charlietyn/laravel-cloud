<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Integration;

use Aws\Exception\AwsException;
use Ronu\LaravelCloud\Contracts\FunctionInvoker;
use Ronu\LaravelCloud\DTO\FunctionInvocation;
use Ronu\LaravelCloud\Support\AwsClientFactory;
use Ronu\LaravelCloud\Tests\FlociIntegrationTestCase;
use ZipArchive;

final class FlociLambdaFunctionInvokerTest extends FlociIntegrationTestCase
{
    public function test_real_lambda_adapter_invokes_function_against_floci(): void
    {
        $zipFile = tempnam(sys_get_temp_dir(), 'laravel-cloud-lambda-');
        self::assertIsString($zipFile);
        $zip = new ZipArchive();
        self::assertTrue($zip->open($zipFile, ZipArchive::OVERWRITE) === true);
        $zip->addFromString('index.mjs', 'export const handler = async (event) => ({ ok: true, input: event });');
        $zip->close();

        $client = $this->app->make(AwsClientFactory::class)->lambda();
        try {
            $client->createFunction([
                'FunctionName' => 'laravel-cloud-tests',
                'Runtime' => getenv('FLOCI_TEST_LAMBDA_RUNTIME') ?: 'nodejs22.x',
                'Role' => 'arn:aws:iam::000000000000:role/lambda-role',
                'Handler' => 'index.handler',
                'Code' => ['ZipFile' => file_get_contents($zipFile)],
                'Timeout' => 10,
            ]);
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() !== 'ResourceConflictException') throw $e;
            $client->updateFunctionCode(['FunctionName' => 'laravel-cloud-tests', 'ZipFile' => file_get_contents($zipFile)]);
        } finally {
            @unlink($zipFile);
        }

        $result = $this->app->make(FunctionInvoker::class)->invoke('default', new FunctionInvocation(['value' => 42]));
        self::assertTrue((bool) ($result->payload['ok'] ?? false));
        self::assertSame(42, $result->payload['input']['value'] ?? null);
    }
}
