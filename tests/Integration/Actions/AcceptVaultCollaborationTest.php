<?php

declare(strict_types=1);

use App\Actions\AcceptVaultCollaboration;
use App\Actions\CreateVaultCollaboration;
use App\Events\NotificationDeletedEvent;
use App\Events\VaultCollaborationAcceptedEvent;
use App\Models\User;
use App\Models\Vault;
use App\Notifications\VaultCollaborationAccepted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('accepts an invitation to a vault', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = Vault::factory()->for($user1)->create();
    app(CreateVaultCollaboration::class)->handle($vault, $user2);

    Event::fake();
    Notification::fake();

    app(AcceptVaultCollaboration::class)->handle($vault, $user2);

    expect($vault->collaborators()->wherePivot('accepted', 1)->exists())->toBeTrue();

    Event::assertDispatched(NotificationDeletedEvent::class);
    Event::assertDispatched(VaultCollaborationAcceptedEvent::class);
    Notification::assertSentTo($user1, VaultCollaborationAccepted::class);
});
