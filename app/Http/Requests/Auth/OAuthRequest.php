<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class OAuthRequest extends FormRequest
{
    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'max:255', 'lowercase'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'provider' => $this->route('provider'),
        ]);
    }
}
