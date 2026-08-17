<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\UploadLimit;
use Illuminate\Foundation\Http\FormRequest;
use Override;

final class ImportVaultRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:zip', 'max:' . UploadLimit::kilobytes()],
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
