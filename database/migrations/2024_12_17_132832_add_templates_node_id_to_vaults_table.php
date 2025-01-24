<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vaults', function (Blueprint $table): void {
            $table->after('name', function (Blueprint $table): void {
                $table->foreignId('templates_node_id')->nullable()->constrained('vault_nodes')->nullOnDelete();
            });
        });
    }
};
