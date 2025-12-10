<?php

declare(strict_types=1);

namespace App\Queries\Vaults;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Database\Eloquent\Builder;

final readonly class VisibleVaultsQuery
{
    /**
     * @return Builder<Vault>
     */
    public function __invoke(User $user): Builder
    {
        return Vault::query()
            ->where('created_by', $user->id)
            ->orWhereHas('collaborators', function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->where('accepted', true);
            })
            ->withCount([
                'collaborators as accepted_collaborators_count' => function (Builder $query): void {
                    $query->where('accepted', true);
                },
            ])
            ->orderBy('updated_at', 'DESC');
    }
}
