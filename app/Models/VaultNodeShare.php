<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\VaultNodeShareFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $vault_node_id
 * @property-read string $token
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read VaultNode $node
 */
final class VaultNodeShare extends Model
{
    /** @use HasFactory<VaultNodeShareFactory> */
    use HasFactory;

    /** @return BelongsTo<VaultNode, $this> */
    public function node(): BelongsTo
    {
        return $this->belongsTo(VaultNode::class, 'vault_node_id');
    }
}
