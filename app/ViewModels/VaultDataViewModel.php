<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Actions\GetVaultPageData;
use App\Models\Vault;
use Illuminate\Support\Collection as SupportCollection;

final readonly class VaultDataViewModel
{
    /**
     * @param SupportCollection<int, VaultNodeViewModel> $recentFiles
     * @param SupportCollection<int, VaultNodeTreeItemViewModel> $rootNodes
     * @param SupportCollection<int, VaultEditorTemplateViewModel>|null $templateNodes
     * @param SupportCollection<int, VaultTagViewModel> $tags
     */
    public function __construct(
        public SupportCollection $recentFiles,
        public SupportCollection $rootNodes,
        public ?SupportCollection $templateNodes,
        public SupportCollection $tags,
    ) {
        //
    }

    public static function fromModel(Vault $vault): self
    {
        $pageData = app(GetVaultPageData::class)->handle($vault);

        return new self(
            $pageData['recentFiles']->map(VaultNodeViewModel::fromModel(...)),
            $pageData['rootNodes']->map(VaultNodeTreeItemViewModel::fromModel(...)),
            $pageData['templateNodes']?->map(VaultEditorTemplateViewModel::fromModel(...)),
            $pageData['tags']->map(VaultTagViewModel::fromModel(...)),
        );
    }
}
