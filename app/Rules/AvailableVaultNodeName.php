<?php

declare(strict_types=1);

namespace App\Rules;

use App\Actions\GetAvailableVaultNodeName;
use App\Models\VaultNode;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class AvailableVaultNodeName implements ValidationRule
{
    public function __construct(private VaultNode $node)
    {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        $available = app(GetAvailableVaultNodeName::class)->handle(
            $this->node->vault,
            $this->node->parent_id,
            $this->node->is_file,
            $value,
            $this->node->extension,
            $this->node->id,
        );

        if ($available !== $value) {
            $fail('The name has already been taken.');
        }
    }
}
