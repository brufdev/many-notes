<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Actions\GetVaultOpenedFileData;
use App\Models\VaultNode;
use Illuminate\Support\Collection as SupportCollection;

final readonly class VaultOpenedFileDataViewModel
{
    /**
     * @param SupportCollection<int, VaultLinkViewModel> $links
     * @param SupportCollection<int, VaultLinkViewModel> $backlinks
     * @param SupportCollection<int, VaultTagViewModel> $tags
     */
    public function __construct(
        public SupportCollection $links,
        public SupportCollection $backlinks,
        public SupportCollection $tags,
    ) {
        //
    }

    public static function fromModel(VaultNode $file): self
    {
        $openedFileData = app(GetVaultOpenedFileData::class)->handle($file);

        return new self(
            $openedFileData['links']->map(VaultLinkViewModel::fromModel(...)),
            $openedFileData['backlinks']->map(VaultLinkViewModel::fromModel(...)),
            $openedFileData['tags']->map(VaultTagViewModel::fromModel(...)),
        );
    }
}
