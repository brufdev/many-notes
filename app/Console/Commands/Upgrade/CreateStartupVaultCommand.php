<?php

declare(strict_types=1);

namespace App\Console\Commands\Upgrade;

use App\Actions\ProcessDiskVault;
use App\Models\User;
use Illuminate\Console\Command;
use Throwable;

final class CreateStartupVaultCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:create-startup-vault';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create startup vault for existing users';

    /**
     * Execute the console command.
     */
    public function handle(ProcessDiskVault $processDiskVault): int
    {
        try {
            $users = User::all();

            foreach ($users as $user) {
                $processDiskVault->handle($user, base_path('assets/Starter Vault'));
            }
        } catch (Throwable) {
            $this->error('Something went wrong processing the starter vault for existing users');

            return self::FAILURE;
        }

        $this->info('All starter vaults were successfully processed');

        return self::SUCCESS;
    }
}
