<?php

declare(strict_types=1);

namespace App\Support;

final readonly class AvailableOAuthProvider
{
    public function __construct(
        public string $name,
        public string $value,
    ) {
        //
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
