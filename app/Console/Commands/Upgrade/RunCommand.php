<?php

declare(strict_types=1);

namespace App\Console\Commands\Upgrade;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class RunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all upgrade commands';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->init();
        $this->processLinks();
        $this->syncDatabaseNotes();
        $this->createStartupVault();
    }

    private function init(): void
    {
        if (DB::table('upgrades')->count()) {
            return;
        }

        DB::table('upgrades')->insert([
            'executed' => 0,
        ]);
    }

    private function processLinks(): void
    {
        /** @var object{executed: int} $upgrades */
        $upgrades = DB::table('upgrades')->first();

        if ($upgrades->executed !== 0) {
            return;
        }

        $this->call('upgrade:process-links');

        DB::table('upgrades')->update([
            'executed' => 1,
        ]);
    }

    private function syncDatabaseNotes(): void
    {
        /** @var object{executed: int} $upgrades */
        $upgrades = DB::table('upgrades')->first();

        if ($upgrades->executed !== 1) {
            return;
        }

        $this->call('upgrade:sync-database-notes');

        DB::table('upgrades')->update([
            'executed' => 2,
        ]);
    }

    private function createStartupVault(): void
    {
        /** @var object{executed: int} $upgrades */
        $upgrades = DB::table('upgrades')->first();

        if ($upgrades->executed !== 2) {
            return;
        }

        $this->call('upgrade:create-startup-vault');

        DB::table('upgrades')->update([
            'executed' => 3,
        ]);
    }
}
