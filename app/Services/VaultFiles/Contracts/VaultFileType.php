<?php

declare(strict_types=1);

namespace App\Services\VaultFiles\Contracts;

interface VaultFileType
{
    /**
     * @return list<string>
     */
    public static function extensions(bool $withDots = false): array;

    public static function validate(string $extension, string $mimeType): bool;
}
