<?php

declare(strict_types=1);

namespace App\Console\Commands\Upgrade;

use App\Models\VaultNode;
use Illuminate\Console\Command;
use Throwable;

final class ReimportDataIntoTypesenseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade:reimport-data-into-typesense';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reimport existing data into Typesense';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->callSilent('scout:flush', ['model' => VaultNode::class]);
            $this->callSilent('scout:import', ['model' => VaultNode::class]);
        } catch (Throwable) {
            $this->error('Something went wrong reimporting existing data into Typesense');

            return self::FAILURE;
        }

        $this->info('All data was successfully reimported into Typesense');

        return self::SUCCESS;
    }
}
