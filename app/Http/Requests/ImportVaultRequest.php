<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Monolog\Utils;

final class ImportVaultRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        /** @var int $maxFileSize */
        $maxFileSize = Utils::expandIniShorthandBytes(ini_get('upload_max_filesize'));

        return [
            'file' => ['required', 'file', 'mimes:zip', 'max:' . $maxFileSize],
        ];
    }
}
