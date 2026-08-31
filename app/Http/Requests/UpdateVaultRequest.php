<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Vault;
use App\Rules\VaultNodeName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

final class UpdateVaultRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        /** @var Vault $vault */
        $vault = $this->route('vault');

        return [
            'name' => [
                'min:1',
                'string',
                'max:255',
                new VaultNodeName(),
                Rule::unique(Vault::class)
                    ->where('created_by', $user->id)
                    ->ignore($this->route('vault')),
            ],
            'templates_node_id' => [
                'integer',
                Rule::exists('vault_nodes', 'id')
                    ->where('vault_id', $vault->id)
                    ->where('is_file', 0),
            ],
        ];
    }

    /** @return array<string, string> */
    #[Override]
    public function messages(): array
    {
        return [
            'name.min' => 'Cannot be empty.',
            'name.max' => 'Cannot be longer than 255 characters.',
        ];
    }
}
