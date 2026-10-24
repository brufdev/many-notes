<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\VaultNode;
use App\Rules\AvailableVaultNodeName;
use App\Rules\FilesystemSafeName;
use App\Rules\VaultSafeName;
use Illuminate\Foundation\Http\FormRequest;
use Override;

final class UpdateVaultNodeRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        /** @var VaultNode $node */
        $node = $this->route('node');

        return [
            'name' => [
                'bail',
                'sometimes',
                'required',
                'string',
                new FilesystemSafeName($node->extension),
                new VaultSafeName(),
                new AvailableVaultNodeName($node),
            ],
            'content' => [
                'nullable',
                'string',
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
