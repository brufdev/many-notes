<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\UploadLimit;
use Illuminate\Foundation\Http\FormRequest;
use Override;

final class ImportVaultNodeRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'min:1'],
            'files' => ['required', 'array'],
            'files.*' => ['required', 'file', 'max:' . UploadLimit::kilobytes()],
        ];
    }

    /** @return array<string, string> */
    #[Override]
    public function messages(): array
    {
        return [
            'files.*.max' => 'Each file must be smaller than ' . UploadLimit::label() . '.',
        ];
    }
}
