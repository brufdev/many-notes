<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ExportVault;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Container\Attributes\CurrentUser;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final readonly class VaultExportController
{
    public function __invoke(
        Vault $vault,
        #[CurrentUser] User $user,
        ExportVault $exportVault,
    ): BinaryFileResponse {
        abort_unless($user->can('view', $vault), 403, __('Not allowed'));

        $path = $exportVault->handle($vault);

        abort_if($path === null, 422, __('Your vault is empty'));

        return response()
            ->download($path, $vault->name . '.zip', [
                'Content-Type' => 'application/zip',
                'X-Content-Type-Options' => 'nosniff',
            ])
            ->deleteFileAfterSend();
    }
}
