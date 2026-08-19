<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\VaultNode;
use Illuminate\Support\Facades\DB;

final readonly class UpdateVaultNodeBacklinks
{
    public function handle(VaultNode $node, string $originalLinkPath, ?string $replacementLinkPath = null): void
    {
        if (!$node->is_file) {
            $this->processFolder($node, $originalLinkPath);

            return;
        }

        $destinationNodes = DB::table('vault_node_vault_node')
            ->select('source_id')
            ->where('destination_id', $node->id);

        $backlinks = VaultNode::query()
            ->whereIn('id', $destinationNodes)
            ->get();

        if ($backlinks->isEmpty()) {
            return;
        }

        if ($replacementLinkPath === null) {
            /**
             * @var string $replacementLinkPath
             *
             * @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall
             */
            $replacementLinkPath = $node->ancestorsAndSelf()->get()->last()->full_path;
        }

        $originalLinkPattern = '\/?'
            . str_replace(' ', '\s', preg_quote($originalLinkPath, '/'))
            . ($node->extension === 'md' ? "(?:\.$node->extension)?" : "\.$node->extension");
        $pattern = <<<REGEX
            /
            \[(.*?)\]                 # Match a markdown-style link text [any text]
            \(                        # Match opening parenthesis "("
                $originalLinkPattern  # Match the original link path
                (\s\".*?\")?          # Optional: Match a title in quotes after the filename
            \)                        # Match closing parenthesis ")"
            /xi
        REGEX;
        $replacement = "[$1](/$replacementLinkPath.$node->extension$2)";

        foreach ($backlinks as $backlink) {
            $content = preg_replace(
                $pattern,
                $replacement,
                (string) $backlink->content,
            );

            app(UpdateVaultNode::class)->handle($backlink, ['content' => $content], true);
        }
    }

    private function processFolder(VaultNode $node, string $originalLinkPath): void
    {
        $descendants = $node->descendants()->where('is_file', true)->get();

        foreach ($descendants as $descendant) {
            $oldLinkPath = '';
            /**
             * @var string $newLinkPath
             *
             * @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall
             */
            $newLinkPath = $descendant->ancestorsAndSelf()->get()->last()->full_path;

            $newLinkPathParts = explode('/', $newLinkPath);
            $originalLinkPathParts = explode('/', $originalLinkPath);

            foreach ($newLinkPathParts as $index => $newLinkPathPart) {
                if ($oldLinkPath !== '') {
                    $oldLinkPath .= '/';
                }

                $oldLinkPath .= array_key_exists($index, $originalLinkPathParts)
                    ? $originalLinkPathParts[$index]
                    : $newLinkPathPart;
            }

            $this->handle($descendant, $oldLinkPath, $newLinkPath);
        }
    }
}
