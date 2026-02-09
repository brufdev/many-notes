<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property-read int $id
 * @property-read bool $registration
 * @property-read bool $auto_update_check
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 */
final class Setting extends Model
{
    #[Override]
    protected function casts(): array
    {
        return [
            'registration' => 'boolean',
            'auto_update_check' => 'boolean',
        ];
    }
}
