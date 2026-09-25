<?php

declare(strict_types=1);

namespace Ronu\LaravelCloud\Tests\Unit;

use Ronu\LaravelCloud\Testing\Fakes\FakeObjectStorage;
use Ronu\LaravelCloud\Tests\TestCase;

final class FakeObjectStorageTest extends TestCase
{
    public function test_fake_can_drive_use_case_tests_without_aws_or_floci(): void
    {
        $storage = new FakeObjectStorage();
        $storage->put('pets/1/avatar.jpg', 'image', 'image/jpeg');
        self::assertTrue($storage->exists('pets/1/avatar.jpg'));
        self::assertSame('image', $storage->get('pets/1/avatar.jpg'));
        self::assertSame('image/jpeg', $storage->metadata('pets/1/avatar.jpg')->contentType);
        $storage->delete('pets/1/avatar.jpg');
        self::assertFalse($storage->exists('pets/1/avatar.jpg'));
    }
}
