<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetPathFromVaultNode;
use App\Actions\GetReferencedImageNodesFromContent;
use App\Actions\GetVaultNodeFromPath;
use App\Models\VaultNode;
use App\Models\VaultNodeShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final readonly class ShareFileController
{
    public function show(
        Request $request,
        VaultNodeShare $share,
        GetVaultNodeFromPath $getVaultNodeFromPath,
        GetReferencedImageNodesFromContent $getReferencedImageNodesFromContent,
        GetPathFromVaultNode $getPathFromVaultNode,
    ): BinaryFileResponse {
        abort_unless($request->has('path'), 404);

        /** @var string $path */
        $path = $request->path;

        $node = $getVaultNodeFromPath->handle($share->node->vault_id, $path);

        abort_unless($node !== null, 404);

        // Only files actually referenced as images in the shared note's current
        // content are servable, regardless of what else lives in the vault.
        $allowedNodeIds = array_map(
            fn(VaultNode $imageNode): int => $imageNode->id,
            $getReferencedImageNodesFromContent->handle($share->node),
        );

        abort_unless(in_array($node->id, $allowedNodeIds, true), 404);

        $relativePath = $getPathFromVaultNode->handle($node);
        $absolutePath = Storage::disk('local')->path($relativePath);

        return response()->file($absolutePath);
    }
}
