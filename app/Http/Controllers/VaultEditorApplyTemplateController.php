<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ApplyTemplateToVaultNode;
use App\Http\Requests\ApplyTemplateToVaultNodeRequest;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Container\Attributes\CurrentUser;

final readonly class VaultEditorApplyTemplateController
{
    public function __invoke(
        ApplyTemplateToVaultNodeRequest $request,
        Vault $vault,
        VaultNode $template,
        #[CurrentUser] User $user,
        ApplyTemplateToVaultNode $applyTemplateToVaultNode,
    ): void {
        abort_unless($user->can('view', $vault), 403);

        /** @var array{ node_id: int } */
        $data = $request->validated();

        /** @var VaultNode $node */
        $node = $vault->nodes()->findOrFail($data['node_id']);

        $applyTemplateToVaultNode->handle($template, $node);
    }
}
