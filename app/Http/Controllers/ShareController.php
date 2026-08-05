<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\VaultNodeShare;
use App\ViewModels\ShareViewModel;
use Inertia\Inertia;
use Inertia\Response;

final readonly class ShareController
{
    public function show(VaultNodeShare $share): Response
    {
        return Inertia::render('share/Show', [
            'share' => ShareViewModel::fromModel($share),
        ]);
    }
}
