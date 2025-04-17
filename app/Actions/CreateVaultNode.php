<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Support\Facades\Storage;

final readonly class CreateVaultNode
{
    /**
     * @param array{
     *   parent_id?: int|null,
     *   is_file: bool,
     *   name: string,
     *   extension?: string|null,
     *   content?: string|null
     * } $attributes
     */
    public function handle(Vault $vault, array $attributes): VaultNode
    {
        $attributes['parent_id'] ??= null;
        $attributes['extension'] ??= null;
        $attributes['content'] ??= null;

        // Generate a new filename if the current one already exists
        $nodeExists = $vault->nodes()
            ->where('parent_id', $attributes['parent_id'])
            ->where('is_file', $attributes['is_file'])
            ->where('name', 'like', $attributes['name'])
            ->where('extension', $attributes['extension'])
            ->exists();

        if ($nodeExists) {
            /** @var list<string> $nodes */
            $nodes = $vault->nodes()
                ->select('name')
                ->where('parent_id', $attributes['parent_id'])
                ->where('is_file', $attributes['is_file'])
                ->where('name', 'like', $attributes['name'] . '-%')
                ->where('extension', $attributes['extension'])
                ->pluck('name')
                ->toArray();
            natcasesort($nodes);
            $attributes['name'] .= count($nodes) && preg_match('/-(\d+)$/', end($nodes), $matches) === 1
                ? '-' . ((int) $matches[1] + 1)
                : '-1';
        }

        // Save node to database
        $databaseContent = $attributes['extension'] === 'md' ? $attributes['content'] : null;
        $node = $vault->nodes()->create([
            'parent_id' => $attributes['parent_id'],
            'is_file' => $attributes['is_file'],
            'name' => $attributes['name'],
            'extension' => $attributes['extension'],
            'content' => $databaseContent,
        ]);

        // Save node to disk
        $nodePath = new GetPathFromVaultNode()->handle($node);

        if ($node->is_file) {
            Storage::disk('local')->put($nodePath, $attributes['content'] ?? '');
        } else {
            Storage::disk('local')->makeDirectory($nodePath);
        }

        return $node;
    }
}
