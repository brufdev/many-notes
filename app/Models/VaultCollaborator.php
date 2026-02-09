<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

/**
 * @property-read int $user_id
 * @property-read int $vault_id
 * @property-read bool $accepted
 */
final class VaultCollaborator extends Pivot
{
    #[Override]
    protected function casts(): array
    {
        return [
            'accepted' => 'boolean',
        ];
    }
}
