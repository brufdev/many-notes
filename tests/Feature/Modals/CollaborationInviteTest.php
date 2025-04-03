<?php

declare(strict_types=1);

use App\Actions\CreateCollaborationInvite;
use App\Actions\CreateVault;
use App\Livewire\Modals\CollaborationInvite;
use App\Models\User;
use App\Notifications\CollaborationAccepted;
use App\Notifications\CollaborationDeclined;
use Livewire\Livewire;

it('accepts the invite', function (): void {
    $user = User::factory()->create()->first();
    $collaborator = User::factory()->create()->first();

    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    new CreateCollaborationInvite()->handle($vault, $collaborator);

    Livewire::actingAs($user)
        ->test(CollaborationInvite::class, ['vault' => $vault])
        ->assertSet('show', false)
        ->call('open', $vault)
        ->assertSet('show', true)
        ->call('accept')
        ->assertSet('show', false);

    $inviteAccepted = $vault->collaborators()
        ->where('user_id', $user->id)
        ->wherePivot('accepted', true)
        ->exists();
    expect($inviteAccepted)->toBeTrue();

    $acceptedNotificationExists = $user->notifications()
        ->where('type', CollaborationAccepted::class)
        ->exists();
    expect($acceptedNotificationExists)->toBeTrue();
});

it('declines the invite', function (): void {
    $user = User::factory()->create()->first();
    $collaborator = User::factory()->create()->first();

    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    new CreateCollaborationInvite()->handle($vault, $collaborator);

    Livewire::actingAs($user)
        ->test(CollaborationInvite::class, ['vault' => $vault])
        ->assertSet('show', false)
        ->call('open', $vault)
        ->assertSet('show', true)
        ->call('decline')
        ->assertSet('show', false);

    $inviteDeclined = !$vault->collaborators()
        ->where('user_id', $user->id)
        ->exists();
    expect($inviteDeclined)->toBeTrue();

    $declinedNotificationExists = $user->notifications()
        ->where('type', CollaborationDeclined::class)
        ->exists();
    expect($declinedNotificationExists)->toBeTrue();
});
