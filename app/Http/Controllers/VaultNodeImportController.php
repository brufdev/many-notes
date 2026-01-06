<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ProcessImportedFile;
use App\Http\Requests\ImportVaultNodeRequest;
use App\Models\User;
use App\Models\Vault;
use App\Services\VaultFile;
use App\ViewModels\VaultTreeNodeViewModel;
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

        foreach ($data['files'] as $file) {
            $fileExtension = $file->getClientOriginalExtension();
            $fileMimeType = $file->getMimeType() ?? '';

            if (!VaultFile::validate($fileExtension, $fileMimeType)) {
                continue;
            }

            $fileName = $file->getClientOriginalName();
            $filePath = $file->getRealPath();
            $node = $processImportedFile->handle($vault, $parent, $fileName, $filePath);

            $importedFiles[] = VaultTreeNodeViewModel::fromModel($node)->toArray();
        }

        return response()->json([
            'files' => $importedFiles,
        ]);
    }
}
