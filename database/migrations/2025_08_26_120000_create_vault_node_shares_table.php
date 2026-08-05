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
        Schema::create('vault_node_shares', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vault_node_id')->unique()->constrained('vault_nodes')->cascadeOnDelete();
            $table->string('token', 48)->unique();
            $table->timestamps();
        });
    }
};
