<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\VaultNode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/** @implements Scope<VaultNode> */
final readonly class SupportedVaultNodeScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->qualifyColumn('is_supported'), true);
    }
}
