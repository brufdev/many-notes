<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetPathFromVaultNode;
use App\Actions\GetVaultNodeFromPath;
use App\Actions\ResolveTwoPaths;
use App\Models\Vault;
use App\Models\VaultNode;
use App\Services\VaultFiles\Types\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final readonly class FileController
{
    /**
     * Show the file for a given user.
     */
    public function show(Vault $vault, Request $request): BinaryFileResponse
    {
        Gate::authorize('view', $request->vault);

        if (!$request->has('path')) {
            abort(404);
        }

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
            $path = new ResolveTwoPaths()->handle($currentPath, $path);
        }

        $node = new GetVaultNodeFromPath()->handle($vault->id, $path);

        if (!$node instanceof VaultNode) {
            abort(404);
        }

        if (in_array($node->extension, Note::extensions())) {
            abort(403);
        }

        $relativePath = new GetPathFromVaultNode()->handle($node);
        $absolutePath = Storage::disk('local')->path($relativePath);

        return response()->file($absolutePath);
    }
}
