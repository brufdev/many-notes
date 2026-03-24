<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultNodeUpdatedEvent;
use App\Models\VaultNode;

final readonly class ApplyTemplateToVaultNode
{
    public function handle(VaultNode $template, VaultNode $node): void
    {
        $now = now();

        $content = str_replace(
            ['{{date}}', '{{time}}'],
            [$now->format('Y-m-d'), $now->format('H:i')],
            (string) $template->content,
        );

        $content = str_contains($content, '{{content}}')
            ? str_replace('{{content}}', (string) $node->content, $content)
            : $content . PHP_EOL . $node->content;

        $node->update(['content' => $content]);

        broadcast(new VaultNodeUpdatedEvent($node));
    }
}
