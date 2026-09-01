<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\VaultNode;
use App\Rules\AvailableVaultNodeName;
use App\Rules\VaultNodeName;
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
                'min:1',
                'string',
                'max:255',
                new VaultNodeName(),
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
            'name.min' => 'Cannot be empty.',
            'name.max' => 'Cannot be longer than 255 characters.',
        ];
    }
}
