<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ExportVault;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

final readonly class VaultExportController
{
    public function __invoke(
        Vault $vault,
        #[CurrentUser] User $user,
        ExportVault $exportVault,
    ): StreamedResponse|JsonResponse {
        if ($user->cannot('view', $vault)) {
            return response()->json([
                'message' => 'You are not authorized to access this resource',
            ], 403);
        }

        try {
            $path = $exportVault->handle($vault);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

        return response()->streamDownload(
            function () use ($path): void {
                $stream = fopen($path, 'rb');

                if ($stream !== false) {
                    fpassthru($stream);
                    fclose($stream);
                    @unlink($path);
                }
            },
            $vault->name . '.zip',
        );
    }
}
