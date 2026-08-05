<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VaultNodeType;
use App\Services\VaultFiles\Types\Audio;
use App\Services\VaultFiles\Types\Image;
use App\Services\VaultFiles\Types\Note;
use App\Services\VaultFiles\Types\Pdf;
use App\Services\VaultFiles\Types\Video;
use Carbon\CarbonImmutable;
use Database\Factories\VaultNodeFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;
use Override;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

/**
 * @property-read int $id
 * @property-read int $vault_id
 * @property-read int|null $parent_id
 * @property-read bool $is_file
 * @property-read string $name
 * @property-read string $extension
 * @property-read string $full_path
 * @property-read string|null $content
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read Vault $vault
 * @property-read Collection<int, VaultNode> $childs
 * @property-read Collection<int, VaultNode> $links
 * @property-read Collection<int, VaultNode> $backlinks
 * @property-read Collection<int, Tag> $tags
 * @property-read VaultNodeShare|null $share
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
        return $this->belongsToMany(self::class, null, 'source_id', 'destination_id')
            ->withPivot('position');
    }

    /** @return BelongsToMany<VaultNode, $this> */
    public function backlinks(): BelongsToMany
    {
        return $this->belongsToMany(self::class, null, 'destination_id', 'source_id')
            ->withPivot('position');
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, null, 'vault_node_id', 'tag_id')
            ->withPivot('position');
    }

    /** @return HasOne<VaultNodeShare, $this> */
    public function share(): HasOne
    {
        return $this->hasOne(VaultNodeShare::class);
    }

    public function isTemplate(): bool
    {
        return $this->vault->templates_node_id !== null
            && $this->parent_id !== null
            && $this->is_file === true
            && $this->extension === 'md'
            /** @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall */
            && $this->ancestors()->get()->contains($this->vault->templates_node_id);
    }

    public function type(): VaultNodeType
    {
        return match (true) {
            in_array($this->extension, Audio::extensions()) => VaultNodeType::AUDIO,
            in_array($this->extension, Note::extensions()) => VaultNodeType::NOTE,
            in_array($this->extension, Image::extensions()) => VaultNodeType::IMAGE,
            in_array($this->extension, Pdf::extensions()) => VaultNodeType::PDF,
            in_array($this->extension, Video::extensions()) => VaultNodeType::VIDEO,
            default => VaultNodeType::FOLDER,
        };
    }

    public function fullPath(): string
    {
        return (string) $this->ancestorsAndSelf()->orderBy('depth')->first()?->full_path;
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
