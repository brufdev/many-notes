<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Vault;
use App\Rules\VaultNodeName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

final class StoreVaultRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
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
                new VaultNodeName(),
                Rule::unique(Vault::class)
                    ->where('created_by', $user->id)
                    ->ignore($this->route('vault')),
            ],
        ];
    }

    /** @return array<string, string> */
    #[Override]
    public function messages(): array
    {
        return [
            'name.required' => 'Cannot be empty.',
            'name.max' => 'Cannot be longer than 255 characters.',
        ];
    }
}
