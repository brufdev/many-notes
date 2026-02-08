<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Override;

final class ResetPasswordRequest extends FormRequest
{
    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => [
                'required',
                'string',
                'max:255',
                'lowercase',
                'email',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'token' => $this->route('token'),
        ]);
    }
}
