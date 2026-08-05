<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VaultNodeType;
use App\Models\VaultNode;

final readonly class GetReferencedImageNodesFromContent
{
    public function __construct(
        private ResolveTwoPaths $resolveTwoPaths,
        private GetVaultNodeFromPath $getVaultNodeFromPath,
    ) {
        //
    }

    /** @return list<VaultNode> */
    public function handle(VaultNode $node): array
    {
        /** @var string $content */
        $content = $node->content ?? '';

        if (preg_match_all('/!\[[^\]]*]\(([^)\s]+)(?:\s+"[^"]*")?\)/', $content, $matches) === false) {
            return [];
        }

        $currentPath = $node->fullPath();
        $imageNodes = [];

        foreach (array_unique($matches[1]) as $path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                continue;
            }

            $resolvedPath = $this->resolveTwoPaths->handle($currentPath, $path);
            $imageNode = $this->getVaultNodeFromPath->handle($node->vault_id, $resolvedPath);

            if ($imageNode !== null && $imageNode->type() === VaultNodeType::IMAGE) {
                $imageNodes[$imageNode->id] = $imageNode;
            }
        }

        return array_values($imageNodes);
    }
}
