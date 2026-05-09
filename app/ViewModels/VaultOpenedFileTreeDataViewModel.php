<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Actions\GetVaultOpenedFileTreeData;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Support\Collection as SupportCollection;

final readonly class VaultOpenedFileTreeDataViewModel
{
    /**
     * @param SupportCollection<int, VaultNodeTreeItemViewModel> $ancestors
     * @param SupportCollection<int, SupportCollection<int, VaultNodeTreeItemViewModel>>|null $ancestorsChildren
     * @param SupportCollection<int, VaultNodeTreeItemViewModel> $siblings
     */
    public function __construct(
        public SupportCollection $ancestors,
        public ?SupportCollection $ancestorsChildren,
        public SupportCollection $siblings,
    ) {
        //
    }

    public static function fromModel(Vault $vault, VaultNode $file): self
    {
        $openedFileTreeData = app(GetVaultOpenedFileTreeData::class)->handle($vault, $file);

        return new self(
            $openedFileTreeData['ancestors']->map(VaultNodeTreeItemViewModel::fromModel(...)),
            $openedFileTreeData['ancestorsChildren']?->map(
                fn(SupportCollection $nodes): SupportCollection => $nodes
                    ->map(VaultNodeTreeItemViewModel::fromModel(...))
                    ->values(),
            ),
            $openedFileTreeData['siblings']->map(VaultNodeTreeItemViewModel::fromModel(...)),
        );
    }
}
