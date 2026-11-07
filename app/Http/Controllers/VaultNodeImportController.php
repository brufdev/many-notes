<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\IsStorableName;
use App\Actions\ProcessImportedFile;
use App\Http\Requests\ImportVaultNodeRequest;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use App\ViewModels\VaultNodeViewModel;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

final readonly class VaultNodeImportController
{
    public function __invoke(
        ImportVaultNodeRequest $request,
        Vault $vault,
        #[CurrentUser] User $user,
        ProcessImportedFile $processImportedFile,
        IsStorableName $isStorableName,
    ): JsonResponse {
        if ($user->cannot('update', $vault)) {
            abort(403);
        }

        /** @var array{parent_id: int|null, files: array<UploadedFile>} $data */
        $data = $request->validated();
        $parent = null;

        if ($data['parent_id'] !== null) {
            $parent = $vault->nodes()
                ->where('id', $data['parent_id'])
                ->where('is_file', false)
                ->first();

            if (!$parent) {
                abort(404);
            }
        }

        $importedFiles = [];
        $passthroughCount = 0;
        $skippedCount = 0;

        foreach ($data['files'] as $file) {
            $fileName = $file->getClientOriginalName();

            if (!$isStorableName->handle($fileName)) {
                $skippedCount++;

                continue;
            }

            $filePath = $file->getRealPath();
            $fileMimeType = $file->getMimeType() ?? '';
            $node = $processImportedFile->handle($vault, $parent, $fileName, $filePath, $fileMimeType);

            if (!$node instanceof VaultNode) {
                $passthroughCount++;

                continue;
            }

            $importedFiles[] = VaultNodeViewModel::fromModel($node);
        }

        return response()->json([
            'files' => $importedFiles,
            'passthrough_count' => $passthroughCount,
            'skipped_count' => $skippedCount,
        ]);
    }
}
