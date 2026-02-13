<?php

declare(strict_types=1);

use App\Actions\CreateVaultCollaboration;
use App\Actions\DeclineVaultCollaboration;
use App\Models\User;
use App\Models\Vault;
use App\ViewModels\VaultCollaborationDeclinedViewModel;

it('maps a VaultCollaborationDeclined notification model to a view model array', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = Vault::factory()->for($user1)->create();

    app(CreateVaultCollaboration::class)->handle($vault, $user2);
    app(DeclineVaultCollaboration::class)->handle($vault, $user2);

    $notification = $user1->notifications()->first();
    $array = VaultCollaborationDeclinedViewModel::fromModel($notification)->toArray();

    expect($array)->toBe([
        'id' => $notification->id,
        'type' => class_basename($notification->type),
        'data' => [
            'user_name' => $user2->name,
        ],
    ]);
});
