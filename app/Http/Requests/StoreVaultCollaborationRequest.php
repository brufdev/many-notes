<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreVaultCollaborationRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'max:255',
                'lowercase',
                'email',
                'exists:users,email',
            ],
        ];
    }
}
