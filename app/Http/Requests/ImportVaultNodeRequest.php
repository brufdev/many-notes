<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ImportVaultNodeRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $uploadMaxFilesize = ini_get('upload_max_filesize') ?: '0';
        $maxFileSize = ini_parse_quantity($uploadMaxFilesize);

        return [
            'parent_id' => ['nullable', 'integer', 'min:1'],
            'files.*' => ['required', 'file', 'max:' . $maxFileSize],
        ];
    }
}
