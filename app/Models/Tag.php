<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VaultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Tag extends Model
{
    /** @use HasFactory<VaultFactory> */
    use HasFactory;
}
