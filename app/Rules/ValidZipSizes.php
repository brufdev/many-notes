<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Number;
use ZipArchive;

final readonly class ValidZipSizes implements ValidationRule
{
    private const int CHUNK_BYTES = 1024 * 1024;

    public function __construct(private int $bytes)
    {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            return;
        }

        $zip = new ZipArchive();

        // Open read-only so closing an empty zip doesn't delete it
        if ($zip->open((string) $value->getRealPath(), ZipArchive::RDONLY) !== true) {
            return;
        }

        $size = 0;

        for ($i = 0, $zipCount = $zip->count(); $i < $zipCount; $i++) {
            $size += $zip->statIndex($i)['size'] ?? 0;
        }

        if ($size > $this->bytes) {
            $fail('The unzipped vault must be smaller than ' . Number::fileSize($this->bytes) . '.');
        } elseif (!$this->hasDeclaredSizes($zip)) {
            $fail('The zip file is damaged.');
        }

        $zip->close();
    }

    private function hasDeclaredSizes(ZipArchive $zip): bool
    {
        for ($i = 0, $zipCount = $zip->count(); $i < $zipCount; $i++) {
            $declaredSize = $zip->statIndex($i)['size'] ?? 0;
            $stream = $zip->getStreamIndex($i);

            if ($stream === false) {
                continue;
            }

            // Stop reading once past the declared size
            $readSize = 0;

            while ($readSize <= $declaredSize && !feof($stream)) {
                $chunk = fread($stream, self::CHUNK_BYTES);

                if ($chunk === false) {
                    break;
                }

                $readSize += mb_strlen($chunk, '8bit');
            }

            fclose($stream);

            if ($readSize !== $declaredSize) {
                return false;
            }
        }

        return true;
    }
}
