<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

final class UpdateVaultRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        return $user->can('update', $this->route('vault'));
    }

    /**
     * @return array<string, array<int, string|Unique>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // One or more allowed characters, not starting with a dot or space
                'regex:/^(?![. ])[\w\s.,;_\-&%#\[\]()=]+$/u',
                Rule::unique(Vault::class)
                    ->where('created_by', $user->id)
                    ->ignore($this->route('vault')),
            ],
        ];
    }
}
