<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Vault extends Model
{
    /** @use HasFactory<\Database\Factories\VaultFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'templates_node_id',
        'name',
    ];

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
