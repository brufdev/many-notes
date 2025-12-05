<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ProcessImportedVault;
use App\Http\Requests\ImportVaultRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;

final readonly class VaultImportController
{
    public function __invoke(
        ImportVaultRequest $request,
        #[CurrentUser] User $user,
        ProcessImportedVault $processImportedVault,
    ): void {
        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->getRealPath();

        $processImportedVault->handle($user, $fileName, $filePath);
    }
}
