<?php

declare(strict_types=1);

namespace App\Rules;

use App\Actions\GetAvailableVaultName;
use App\Models\User;
use App\Models\Vault;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class AvailableVaultName implements ValidationRule
{
    public function __construct(private User $owner, private ?Vault $vault = null)
    {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        $available = app(GetAvailableVaultName::class)->handle($this->owner, $value, $this->vault);

        if ($available !== $value) {
            $fail('The name has already been taken.');
        }
    }
}
