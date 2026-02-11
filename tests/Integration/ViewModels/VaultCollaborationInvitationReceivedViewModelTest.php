<?php

declare(strict_types=1);

use App\Actions\CreateVaultCollaboration;
use App\Models\User;
use App\Models\Vault;
use App\ViewModels\VaultCollaborationInvitationReceivedViewModel;

it('maps a VaultCollaborationInvitationReceived notification model to a view model array', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = Vault::factory()->for($user1)->create();

    app(CreateVaultCollaboration::class)->handle($vault, $user2);

    $notification = $user2->notifications()->first();
    $array = VaultCollaborationInvitationReceivedViewModel::fromModel($notification)->toArray();

    expect($array)->toBe([
        'id' => $notification->id,
        'type' => class_basename($notification->type),
        'data' => [
            'user_name' => $user1->name,
            'vault_id' => $vault->id,
            'vault_name' => $vault->name,
        ],
    ]);
});
