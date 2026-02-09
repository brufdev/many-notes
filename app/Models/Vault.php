<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\VaultFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Override;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read int $created_by
 * @property-read CarbonImmutable|null $opened_at
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read User $user
 * @property-read Collection<int, VaultNode> $nodes
 * @property-read Collection<int, Tag> $tags
 * @property-read VaultNode|null $templatesNode
 * @property-read Collection<int, User> $collaborators
 */
final class Vault extends Model
{
    /** @use HasFactory<VaultFactory> */
    use HasFactory;

    use HasRelationships;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<VaultNode, $this> */
    public function nodes(): HasMany
    {
        return $this->hasMany(VaultNode::class);
    }

    /** @return HasManyDeep<Model, $this> */
    public function tags(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->nodes(), new VaultNode()->tags());
    }

    /** @return HasOne<VaultNode, $this> */
    public function templatesNode(): HasOne
    {
        return $this->hasOne(VaultNode::class, 'id', 'templates_node_id');
    }

    /** @return BelongsToMany<User, $this, VaultCollaborator> */
    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('accepted')
            ->using(VaultCollaborator::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
        ];
    }
}
