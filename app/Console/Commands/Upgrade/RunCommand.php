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
        $commands = [
            'upgrade:process-links',
            'upgrade:sync-database-notes',
            'upgrade:create-startup-vault',
            'upgrade:reimport-data-into-typesense',
            'upgrade:set-super-admin',
            'upgrade:process-links',
        ];

        $this->executeCommands($commands);
    }

    /**
     * @param list<string> $commands
     */
    private function executeCommands(array $commands): void
    {
        $commandsExecuted = $this->getTotalCommandsExecuted();
        $commandsTotal = count($commands);

        for ($i = $commandsExecuted; $i < $commandsTotal; $i++) {
            $this->call($commands[$i]);
            DB::table('upgrades')->update(['executed' => $i + 1]);
        }
    }

    private function getTotalCommandsExecuted(): int
    {
        if (DB::table('upgrades')->count() > 0) {
            /** @var object{executed: int} $upgrades */
            $upgrades = DB::table('upgrades')->first();

            return $upgrades->executed;
        }

        DB::table('upgrades')->insert(['executed' => 0]);

        return 0;
    }
}
