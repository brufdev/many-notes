<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Vault;
use App\Rules\VaultNodeName;
use App\Services\VaultFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

final class StoreVaultNodeRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Vault $vault */
        $vault = $this->route('vault');

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('vault_nodes', 'id')
                    ->where('vault_id', $vault->id),
            ],
            'is_file' => [
                'required',
                'boolean',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                new VaultNodeName(),
            ],
            'extension' => [
                'nullable',
                'string',
                Rule::in(VaultFile::extensions()),
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
