<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Vault;
use App\Services\VaultFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Unique;

final class StoreVaultNodeRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string|Exists|In|Unique>>
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
                'min:1',
                'max:255',
                // One or more allowed characters, not starting with a dot or space
                'regex:/^(?![. ])[\w\s.,;_\-&%#\[\]()=]+$/u',
            ],
            'extension' => [
                'nullable',
                'string',
                Rule::in(VaultFile::extensions()),
            ],
        ];
    }
}
