<?php

declare(strict_types=1);

namespace App\Console\Commands\Upgrade;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Throwable;

final class SetSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:set-super-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the oldest user as Super Admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $oldestUser = User::oldest('created_at')->first();

            if (!$oldestUser instanceof User) {
                $this->info('The first registered user will be assigned as Super Admin');

                return self::SUCCESS;
            }

            $oldestUser->update(['role' => UserRole::SUPER_ADMIN]);
        } catch (Throwable) {
            $this->error('Something went wrong setting the oldest user as Super Admin');

            return self::FAILURE;
        }

        $this->info('The oldest user was successfully promoted to Super Admin');

        return self::SUCCESS;
    }
}
