<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateVaultTemplatesNodeRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'templates_node_id' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }
}
