<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Actions\IsLocalAuthEnabled;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class PasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $isLocalAuthEnabled = app(IsLocalAuthEnabled::class);

        return $isLocalAuthEnabled->handle();
    }

    /**
     * @return array<string, array<int, string|Password>>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::defaults() ?? 'min:8', 'confirmed'],
        ];
    }
}
