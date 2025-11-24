<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property UserRole $role
 */
final class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use Notifiable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Indicate if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        /** @var UserRole $role */
        $role = $this->role;

        return $role === UserRole::SUPER_ADMIN;
    }

    /**
     * Indicate if the user is an admin.
     */
    public function isAdmin(): bool
    {
        /** @var UserRole $role */
        $role = $this->role;

        return $role === UserRole::ADMIN || $role === UserRole::SUPER_ADMIN;
    }

    /**
     * Get the associated social accounts.
     *
     * @return HasMany<SocialAccount, $this>
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get the associated vaults.
     *
     * @return HasMany<Vault, $this>
     */
    public function vaults(): HasMany
    {
        return $this->hasMany(Vault::class, 'created_by');
    }

    /**
     * Get the collaborations that belongs to the user.
     *
     * @return BelongsToMany<Vault, $this>
     */
    public function collaborations(): BelongsToMany
    {
        return $this->belongsToMany(Vault::class)->withPivot('accepted');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
}
