<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetPathFromVaultNode;
use App\Actions\GetVaultNodeFromPath;
use App\Actions\ResolveTwoPaths;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use App\Services\VaultFile;
use App\Services\VaultFiles\Types\Note;
use App\Services\VaultFiles\Types\Pdf;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final readonly class FileController
{
    public function show(
        Request $request,
        Vault $vault,
        #[CurrentUser] User $user,
        ResolveTwoPaths $resolveTwoPaths,
        GetVaultNodeFromPath $getVaultNodeFromPath,
        GetPathFromVaultNode $getPathFromVaultNode,
    ): BinaryFileResponse {
        abort_unless($user->can('view', $vault), 403);

        abort_unless($request->has('path'), 404);

        /** @var string $path */
        $path = $request->path;

        if (!str_starts_with($path, '/') && $request->has('node')) {
            /** @var VaultNode $node */
            $node = $vault->nodes()->findOrFail($request->node);

            /**
             * @var string $currentPath
             *
             * @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall
             */
            $currentPath = $node->ancestorsAndSelf()->get()->last()->full_path;
            $path = $resolveTwoPaths->handle($currentPath, $path);
        }

        $node = $getVaultNodeFromPath->handle($vault->id, $path);

        abort_unless($node instanceof VaultNode, 404);

        abort_if(in_array($node->extension, Note::extensions()), 403);

        $contentType = VaultFile::contentType((string) $node->extension);

        abort_if($contentType === null, 404);

        $relativePath = $getPathFromVaultNode->handle($node);
        $absolutePath = Storage::disk('local')->path($relativePath);
        $headers = [
            'Content-Type' => $contentType,
            'X-Content-Type-Options' => 'nosniff',
        ];

        if (!in_array($node->extension, Pdf::extensions())) {
            $headers['Content-Security-Policy'] = 'sandbox';
        }

        return response()->file($absolutePath, $headers);
    }
}
