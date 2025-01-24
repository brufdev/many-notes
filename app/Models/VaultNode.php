<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\VaultNodeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

#[ObservedBy([VaultNodeObserver::class])]
final class VaultNode extends Model
{
    use HasFactory;
    use HasRecursiveRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
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
     */
    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class);
    }

    /**
     * Get the nodes for the vault.
     */
    public function childs(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

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
