<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateZipFromDirectory;
use App\Actions\GetPathFromVaultNode;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final readonly class VaultNodeDownloadController
{
    public function __invoke(
        Vault $vault,
        VaultNode $node,
        #[CurrentUser] User $user,
        GetPathFromVaultNode $getPathFromVaultNode,
        CreateZipFromDirectory $createZipFromDirectory,
    ): BinaryFileResponse {
        abort_unless($user->can('view', $vault), 403, __('Not allowed'));

        $disk = Storage::disk('local');
        $path = $getPathFromVaultNode->handle($node);

        if ($node->is_file) {
            abort_unless($disk->exists($path), 404);

            $fileName = $node->name . ($node->extension === null ? '' : ".{$node->extension}");
            $fallbackName = str_replace('%', '', Str::ascii($fileName)) ?: 'download';
            $response = new BinaryFileResponse($disk->path($path), 200, [
                'Content-Type' => 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ]);

            return $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $fileName,
                $fallbackName,
            );
        }

        $zipPath = $createZipFromDirectory->handle($path);

        abort_if($zipPath === null, 422, __('This folder is empty'));

        return response()
            ->download($zipPath, $node->name . '.zip', [
                'Content-Type' => 'application/zip',
                'X-Content-Type-Options' => 'nosniff',
            ])
            ->deleteFileAfterSend();
    }
}
