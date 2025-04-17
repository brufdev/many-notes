<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;
use App\Models\VaultNode;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Collection;
use ZipArchive;

final readonly class ExportVault
{
    public function handle(Vault $vault): string
    {
        $zip = new ZipArchive();
        $relativePath = 'public/' . Str::random(16) . '.zip';
        $path = Storage::disk('local')->path($relativePath);
        $nodes = $vault->nodes()->whereNull('parent_id')->get();

        if ($nodes->count() === 0) {
            throw new Exception(__('Your vault is empty'));
        }

        Storage::disk('local')->put($relativePath, '');

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception(__('Something went wrong'));
        }

        $this->exportNodes($zip, $nodes);
        $zip->close();

        return $path;
    }

    /**
     * @param  Collection<int, VaultNode>  $nodes
     */
    private function exportNodes(ZipArchive &$zip, Collection $nodes, string $path = ''): void
    {
        foreach ($nodes as $node) {
            $nodePath = mb_ltrim("$path/$node->name", '/');
            $nodePath .= $node->is_file ? ".$node->extension" : '';
            $relativePath = new GetPathFromVaultNode()->handle($node);

            if (!Storage::disk('local')->exists($relativePath)) {
                throw new Exception(
                    sprintf(
                        "%s missing on disk: {$nodePath}",
                        $node->is_file ? 'File' : 'Folder',
                    ),
                );
            }

            if (!$node->is_file) {
                $zip->addEmptyDir($nodePath);

                if ($node->children()->count()) {
                    $this->exportNodes($zip, $node->children()->get(), $nodePath);
                }
            } elseif ($node->extension === 'md') {
                $zip->addFromString($nodePath, (string) $node->content);
            } else {
                $relativePath = new GetPathFromVaultNode()->handle($node);

                $zip->addFile(
                    Storage::disk('local')->path($relativePath),
                    $nodePath,
                );
            }
        }
    }
}
