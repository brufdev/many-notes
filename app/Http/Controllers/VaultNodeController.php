<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateVaultNode;
use App\Http\Requests\StoreVaultNodeRequest;
use App\Models\Vault;
use Illuminate\Http\RedirectResponse;

final readonly class VaultNodeController
{
    public function store(
        StoreVaultNodeRequest $request,
        Vault $vault,
        CreateVaultNode $createVaultNode,
    ): RedirectResponse {
        /**
         * @var array{
         *   parent_id?: int|null,
         *   name: string,
         *   is_file: bool,
         * } $data
         */
        $data = $request->validated();
        $data['extension'] = $data['is_file'] ? 'md' : null;

        $node = $createVaultNode->handle($vault, $data);

        if ($node->is_file) {
            return redirect()->route('vaults.show', [
                'vault' => $vault->id,
                'file' => $node->id,
            ]);
        }

        return redirect()->back();
    }
}
