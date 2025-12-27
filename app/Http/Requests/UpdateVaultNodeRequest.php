<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateVaultNodeRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'min:1',
                'max:255',
                // One or more allowed characters, not starting with a dot or space
                'regex:/^(?![. ])[\w\s.,;_\-&%#\[\]()=]+$/u',
            ],
        ];
    }
}
