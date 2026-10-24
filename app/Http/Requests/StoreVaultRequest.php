<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Vault;
use App\Rules\FilesystemSafeName;
use App\Rules\VaultSafeName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

final class StoreVaultRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        return [
            'name' => [
                'bail',
                'required',
                'string',
                new FilesystemSafeName(),
                new VaultSafeName(),
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
        ];
    }
}
