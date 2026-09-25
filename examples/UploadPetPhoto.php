<?php

declare(strict_types=1);

use Ronu\LaravelCloud\Contracts\ObjectStorage;

final readonly class UploadPetPhoto
{
    public function __construct(private ObjectStorage $storage) {}

    public function execute(string $petId, string $contents): void
    {
        $this->storage->put("pets/{$petId}/avatar.jpg", $contents, 'image/jpeg');
    }
}
