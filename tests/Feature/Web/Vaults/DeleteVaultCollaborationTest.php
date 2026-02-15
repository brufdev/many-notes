<?php

declare(strict_types=1);

use App\Events\NotificationDeletedEvent;
use App\Events\VaultCollaborationAccessRevokedEvent;
use App\Events\VaultCollaborationDeletedEvent;
use App\Models\User;
use App\Notifications\VaultCollaborationInvitationReceived;
use Illuminate\Support\Facades\Event;

it('removes a collaborator', function (): void {
    Event::fake();

    [$user1, $user2] = User::factory(2)->create();
    $vault = $user1->vaults()->create([
        'name' => fake()->words(3, true),
    ]);
    $vault->collaborators()->attach($user2, ['accepted' => 1]);
    expect($vault->collaborators()->count())->toBe(1);

    $this->actingAs($user1);

    $response = $this->delete(
        route('vaults.collaborations.destroy', ['vault' => $vault->id, 'user' => $user2->id]),
    );

    $response->assertStatus(200);
    expect($vault->collaborators()->count())->toBe(0);

    Event::assertDispatched(VaultCollaborationDeletedEvent::class);
    Event::assertDispatched(VaultCollaborationAccessRevokedEvent::class);
});

it('removes an invited user', function (): void {
    Event::fake();

    [$user1, $user2] = User::factory(2)->create();
    $vault = $user1->vaults()->create([
        'name' => fake()->words(3, true),
    ]);
    $vault->collaborators()->attach($user2, ['accepted' => 0]);
    $user2->notify(new VaultCollaborationInvitationReceived($vault));
    expect($vault->collaborators()->count())->toBe(1);

    $this->actingAs($user1);

    $response = $this->delete(
        route('vaults.collaborations.destroy', ['vault' => $vault->id, 'user' => $user2->id]),
    );

    $response->assertStatus(200);
    expect($vault->collaborators()->count())->toBe(0);

    Event::assertDispatched(NotificationDeletedEvent::class);
    Event::assertDispatched(VaultCollaborationDeletedEvent::class);
});

it('only allows the vault owner to remove collaborators', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = $user1->vaults()->create([
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user2);

    $response = $this->delete(
        route('vaults.collaborations.destroy', ['vault' => $vault->id, 'user' => $user1->id]),
    );

    $response->assertStatus(403);
});

it('does not allow to invite non existing users to collaborate', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = $user1->vaults()->create([
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user2);

    $response = $this->delete(
        route('vaults.collaborations.destroy', ['vault' => $vault->id, 'user' => $user2->id]),
    );

    $response->assertStatus(404);
});
