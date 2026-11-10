<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Vault;
use App\Rules\AvailableVaultName;
use App\Rules\FilesystemSafeName;
use App\Rules\VaultSafeName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

final class UpdateVaultRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        /** @var Vault $vault */
        $vault = $this->route('vault');
        $vault->loadMissing('user');

        return [
            'name' => [
                'bail',
                'sometimes',
                'required',
                'string',
                new FilesystemSafeName(),
                new VaultSafeName(),
                new AvailableVaultName($vault->user, $vault),
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
            'name.required' => 'Cannot be empty.',
        ];
    }
}
