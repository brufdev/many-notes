<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

final class Vault extends Model
{
    /** @use HasFactory<\Database\Factories\VaultFactory> */
    use HasFactory;

    use HasRelationships;

    /**
     * Get the associated user.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the nodes for the vault.
     *
     * @return HasMany<VaultNode, $this>
     */
    public function nodes(): HasMany
    {
        return $this->hasMany(VaultNode::class);
    }

    /**
     * Get the tags for the vault.
     *
     * @return HasManyDeep<Model, $this>
     */
    public function tags(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->nodes(), new VaultNode()->tags());
    }

    /**
     * Get the associated templates node.
     *
     * @return HasOne<VaultNode, $this>
     */
    public function templatesNode(): HasOne
    {
        return $this->hasOne(VaultNode::class, 'id', 'templates_node_id');
    }

    /**
     * Get the collaborators that belongs to the vault.
     *
     * @return BelongsToMany<User, $this>
     */
    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('accepted');
    }
}
