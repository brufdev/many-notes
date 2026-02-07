<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Setting extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'registration' => 'boolean',
            'auto_update_check' => 'boolean',
        ];
    }
}
