<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('only lists the user vaults', function (): void {
    $user = User::factory(2)->hasVaults(2)->create()->first();

    $this->actingAs($user);

    $this->get(route('vaults.index'))
        ->assertInertia(
            fn(Assert $page): Assert => $page->has('visibleVaults', 2)
        );
});
