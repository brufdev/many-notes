<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

final class VaultNode extends Model
{
    /** @use HasFactory<\Database\Factories\VaultNodeFactory> */
    use HasFactory;

    use HasRecursiveRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'is_file',
        'name',
        'extension',
        'content',
    ];

    /**
     * Get the associated vault.
     *
     * @return BelongsTo<Vault, $this>
     */
    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class);
    }

    /**
     * Get the childs for the node.
     *
     * @return HasMany<VaultNode, $this>
     */
    public function childs(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * The nodes that are linked to the node.
     *
     * @return BelongsToMany<VaultNode, $this>
     */
    public function links(): BelongsToMany
    {
        return $this->belongsToMany(self::class, null, 'source_id', 'destination_id');
    }

    /**
     * The nodes that are backlinked to the node.
     *
     * @return BelongsToMany<VaultNode, $this>
     */
    public function backlinks(): BelongsToMany
    {
        return $this->belongsToMany(self::class, null, 'destination_id', 'source_id');
    }

    /**
     * Get the custom paths for the model.
     *
     * @return list<array{name: string, column: string, separator: string, reverse: bool}>
     */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_file' => 'boolean',
        ];
    }
}
