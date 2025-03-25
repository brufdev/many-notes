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
        Schema::create('user_vault', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('vault_id')->constrained('vaults');
            $table->unsignedTinyInteger('accepted');

            $table->primary(['user_id', 'vault_id', 'accepted']);
        });
    }
};
