<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                'string',
                'max:255',
                // One or more allowed characters, not starting with a dot or space
                'regex:/^(?![. ])[\w\s.,;_\-&%#\[\]()=]+$/u',
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
}
