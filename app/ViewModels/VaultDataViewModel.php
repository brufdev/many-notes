<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Actions\GetVaultPageData;
use App\Models\Vault;
use Illuminate\Support\Collection as SupportCollection;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

final readonly class VaultDataViewModel implements ProvidesInertiaProperties
{
    public function __construct(private Vault $vault)
    {
        //
    }

    public static function fromModel(Vault $vault): self
    {
        return new self($vault);
    }

    /** @return array<string, mixed> */
    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'recentFiles' => $this->recentFiles(...),
            'rootNodes' => $this->rootNodes(...),
            'tags' => $this->tags(...),
            'templateNodes' => $this->templateNodes(...),
        ];
    }

    /** @return SupportCollection<int, VaultNodeViewModel> */
    private function recentFiles(): SupportCollection
    {
        return app(GetVaultPageData::class)->handle($this->vault)['recentFiles']()
            ->map(VaultNodeViewModel::fromModel(...));
    }

    /** @return SupportCollection<int, VaultNodeTreeItemViewModel> */
    private function rootNodes(): SupportCollection
    {
        return app(GetVaultPageData::class)->handle($this->vault)['rootNodes']()
            ->map(VaultNodeTreeItemViewModel::fromModel(...));
    }

    /** @return SupportCollection<int, VaultTagViewModel> */
    private function tags(): SupportCollection
    {
        return app(GetVaultPageData::class)->handle($this->vault)['tags']()
            ->map(VaultTagViewModel::fromModel(...));
    }

    /** @return SupportCollection<int, VaultEditorTemplateViewModel>|null */
    private function templateNodes(): ?SupportCollection
    {
        return app(GetVaultPageData::class)->handle($this->vault)['templateNodes']()
            ?->map(VaultEditorTemplateViewModel::fromModel(...));
    }
}
