<?php

declare(strict_types=1);

use App\Events\VaultCollaborationCreatedEvent;
use App\Models\User;
use App\Notifications\VaultCollaborationInvitationReceived;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('invites a user to a vault', function (): void {
    Event::fake();
    Notification::fake();

    [$user1, $user2] = User::factory(2)->create();
    $vault = $user1->vaults()->create([
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user1);

    $response = $this->post(
        route('vaults.collaborations.store', ['vault' => $vault->id]),
        ['email' => $user2->email],
    );

    $response->assertStatus(200);
    expect($vault->collaborators()->count())->toBe(1);

    Notification::assertSentTo($user2, VaultCollaborationInvitationReceived::class);
    Event::assertDispatched(VaultCollaborationCreatedEvent::class);
});

it('only allows the vault owner to invite users to collaborate', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = $user1->vaults()->create([
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user2);

    $response = $this->post(
        route('vaults.collaborations.store', ['vault' => $vault->id]),
        ['email' => $user2->email],
    );

    $response->assertStatus(403);
});

it('does not allow the vault owner to invite himself', function (): void {
    $user = User::factory()->create();
    $vault = $user->vaults()->create([
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user);

    $response = $this->post(
        route('vaults.collaborations.store', ['vault' => $vault->id]),
        ['email' => $user->email],
    );

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'email' => 'You are the owner of this vault',
    ]);
});

it('does not allow to invite users that are already collaborating', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = $user1->vaults()->create([
        'name' => fake()->words(3, true),
    ]);
    $vault->collaborators()->attach($user2, ['accepted' => 1]);

    $this->actingAs($user1);

    $response = $this->post(
        route('vaults.collaborations.store', ['vault' => $vault->id]),
        ['email' => $user2->email],
    );

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'email' => 'User is already a collaborator',
    ]);
});

it('does not allow to invite users that are already invited', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = $user1->vaults()->create([
        'name' => fake()->words(3, true),
    ]);
    $vault->collaborators()->attach($user2, ['accepted' => 0]);

    $this->actingAs($user1);

    $response = $this->post(
        route('vaults.collaborations.store', ['vault' => $vault->id]),
        ['email' => $user2->email],
    );

    $response->assertStatus(302);
    $response->assertSessionHasErrors([
        'email' => 'User is already invited',
    ]);
});
