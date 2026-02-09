<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\VaultNodeFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Override;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

/**
 * @property-read int $id
 * @property-read int $vault_id
 * @property-read int $parent_id
 * @property-read bool $is_file
 * @property-read string $name
 * @property-read string $extension
 * @property-read string|null $content
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read Vault $vault
 * @property-read Collection<int, VaultNode> $childs
 * @property-read Collection<int, VaultNode> $links
 * @property-read Collection<int, VaultNode> $backlinks
 * @property-read Collection<int, Tag> $tags
 */
final class VaultNode extends Model
{
    /** @use HasFactory<VaultNodeFactory> */
    use HasFactory;

    use HasRecursiveRelationships;
    use Searchable;

    /** @return BelongsTo<Vault, $this> */
    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class);
    }

    /** @return HasMany<VaultNode, $this> */
    public function childs(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return BelongsToMany<VaultNode, $this> */
    public function links(): BelongsToMany
    {
        return $this->belongsToMany(self::class, null, 'source_id', 'destination_id');
    }

    /** @return BelongsToMany<VaultNode, $this> */
    public function backlinks(): BelongsToMany
    {
        return $this->belongsToMany(self::class, null, 'destination_id', 'source_id');
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, null, 'vault_node_id', 'tag_id');
    }

    /** @return list<array{name: string, column: string, separator: string, reverse: bool}> */
    public function getCustomPaths(): array
    {
        return [
            [
                'name' => 'full_path',
                'column' => 'name',
                'separator' => '/',
                'reverse' => true,
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'vault_id' => (string) $this->vault_id,
            'name' => $this->name,
            'content' => (string) $this->content,
            'updated_at' => $this->updated_at->timestamp,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_file;
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'is_file' => 'boolean',
        ];
    }
}
