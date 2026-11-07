<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ValidZipSizes;
use App\Services\UploadLimit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

final class ImportVaultRequest extends FormRequest
{
    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        return [
            'file' => [
                'bail',
                'required',
                'file',
                'mimes:zip',
                'max:' . UploadLimit::kilobytes(),
                new ValidZipSizes(UploadLimit::unzippedBytes()),
            ],
        ];
    }

    /** @return array<string, string> */
    #[Override]
    public function messages(): array
    {
        return [
            'file.max' => 'The vault archive must be smaller than ' . UploadLimit::label() . '.',
        ];
    }
}
