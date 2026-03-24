<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ApplyTemplateToVaultNodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Vault $vault */
        $vault = $this->route('vault');

        /** @var VaultNode $template */
        $template = $this->route('template');

        return $template->vault_id === $vault->id && $template->isTemplate();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        /** @var Vault $vault */
        $vault = $this->route('vault');

        return [
            'node_id' => [
                'integer',
                Rule::exists('vault_nodes', 'id')
                    ->where('vault_id', $vault->id)
                    ->where('is_file', 1)
                    ->where('extension', 'md'),
            ],
        ];
    }
}
