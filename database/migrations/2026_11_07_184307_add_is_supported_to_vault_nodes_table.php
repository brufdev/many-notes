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
        Schema::table('vault_nodes', function (Blueprint $table): void {
            $table->boolean('is_supported')->default(true);

            $table->index('is_supported');
        });
    }
};
