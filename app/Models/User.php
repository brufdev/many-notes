<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;

/**
 * @property UserRole $role
 */
final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role === UserRole::ADMIN;
    }

    /** @return HasMany<SocialAccount, $this> */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /** @return HasMany<Vault, $this> */
    public function vaults(): HasMany
    {
        return $this->hasMany(Vault::class, 'created_by');
    }

    /** @return BelongsToMany<Vault, $this> */
    public function collaborations(): BelongsToMany
    {
        return $this->belongsToMany(Vault::class)->withPivot('accepted');
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
}
