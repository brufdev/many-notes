<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Models\Vault;
use App\Models\VaultNode;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class SearchNode extends Component
{
    use Modal;

    public Vault $vault;

    /**
     * @var array<
     *   array{
     *     id: string,
     *     name: string,
     *     content: string,
     *     time_elapsed: string,
     *   }
     * >
     */
    #[Locked]
    public array $nodes = [];

    public int $selectedNode = 0;

    public string $search = '';

    public function mount(Vault $vault): void
    {
        $this->authorize('view', $vault);
        $this->vault = $vault;
    }

    #[On('open-modal')]
    public function open(string $search = ''): void
    {
        $this->openModal();
        $this->search = $search;
    }

    public function search(): void
    {
        $this->reset('nodes', 'selectedNode');

        if ($this->search === '') {
            return;
        }

        preg_match('/tag:([\p{L}0-9_-]+)/u', $this->search, $matches);
        $this->nodes = $matches === [] ? $this->searchText() : $this->searchTag($matches[1]);
    }

    public function render(): Factory|View
    {
        $this->search();

        return view('livewire.modals.searchNode');
    }

    /**
     * Searches for nodes with a given name.
     *
     * @return array<
     *   array{
     *     id: string,
     *     name: string,
     *     content: string,
     *     time_elapsed: string,
     *   }
     * >
     */
    private function searchText(): array
    {
        /**
         * @var array{
         *   hits: list<
         *     array{
         *       document: array{
         *         id: string,
         *         name: string,
         *         content: string,
         *         updated_at: string
         *       },
         *       highlight: array{
         *         name?: array{
         *           snippet: string
         *         },
         *         content?: array{
         *           snippet: string
         *         }
         *       }
         *     }
         *   >
         * }
         */
        $rawResults = VaultNode::search($this->search)
            ->where('vault_id', $this->vault->id)
            ->raw();

        $results = [];

        foreach ($rawResults['hits'] as $hit) {
            $results[] = [
                'id' => $hit['document']['id'],
                'name' => $this->encodeText(
                    $hit['highlight']['name']['snippet'] ?? $hit['document']['name'],
                ),
                'content' => $this->encodeText(
                    $hit['highlight']['content']['snippet'] ?? $hit['document']['content'],
                ),
                'time_elapsed' => new CarbonImmutable($hit['document']['updated_at'])
                    ->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE, short: true),
            ];
        }

        return $results;
    }

    /**
     * Searches for nodes with a given tag.
     *
     * @return array<
     *   array{
     *     id: string,
     *     name: string,
     *     content: string,
     *     time_elapsed: string,
     *   }
     * >
     */
    private function searchTag(string $tag): array
    {
        $nodes = VaultNode::query()
            ->select('id', 'name', 'extension', 'updated_at')
            ->where('vault_id', $this->vault->id)
            ->where('is_file', true)
            ->whereHas('tags', fn(IlluminateBuilder $query): IlluminateBuilder => $query->where('name', $tag))
            ->orderByDesc('updated_at')
            ->get();

        $results = [];

        foreach ($nodes as $node) {
            /** @var CarbonImmutable $updatedAt */
            $updatedAt = $node->updated_at;
            $timeElapsed = $updatedAt->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE, short: true);

            $results[] = [
                'id' => (string) $node->id,
                'name' => $this->encodeText((string) $node->name),
                'content' => '',
                'time_elapsed' => $timeElapsed,
            ];
        }

        return $results;
    }

    private function encodeText(string $text): string
    {
        return preg_replace('/<(?!\/?mark>)/', '&lt;', $text) ?? '';
    }
}
