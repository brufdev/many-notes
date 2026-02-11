<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateVaultCollaboration;
use App\Http\Requests\StoreVaultCollaborationRequest;
use App\Models\User;
use App\Models\Vault;
use App\ViewModels\VaultCollaboratorViewModel;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final readonly class VaultCollaborationController
{
    public function store(
        StoreVaultCollaborationRequest $request,
        Vault $vault,
        #[CurrentUser] User $currentUser,
        CreateVaultCollaboration $createVaultCollaboration,
    ): JsonResponse {
        abort_unless($currentUser->id === $vault->user->id, 403);

        /** @var array{email: string} $validated */
        $validated = $request->validated();

        /** @var User $invitedUser */
        $invitedUser = User::where('email', $validated['email'])->first();

        if ($invitedUser->id === $currentUser->id) {
            throw ValidationException::withMessages([
                'email' => __('You are the owner of this vault'),
            ]);
        }

        try {
            $collaborator = $vault->collaborators()
                ->wherePivot('user_id', $invitedUser->id)
                ->firstOrFail();

            $message = $collaborator->pivot->accepted
                ? __('User is already a collaborator')
                : __('User is already invited');

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        } catch (ModelNotFoundException) {
            $collaborator = $createVaultCollaboration->handle($vault, $invitedUser);

            return response()->json([
                'data' => VaultCollaboratorViewModel::fromModel($collaborator)->toArray(),
            ]);
        }
    }
}
