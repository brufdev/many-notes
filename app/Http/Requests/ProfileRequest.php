<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Actions\IsLocalAuthEnabled;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

final class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $isLocalAuthEnabled = app(IsLocalAuthEnabled::class);

        return $isLocalAuthEnabled->handle();
    }

    /**
     * @return array<string, array<int, string|Unique>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'max:255',
                'lowercase',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ];
    }
}
