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
        Schema::create('vault_nodes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vault_id')->constrained('vaults');
            $table->foreignId('parent_id')->nullable()->constrained('vault_nodes');
            $table->unsignedTinyInteger('is_file');
            $table->string('name');
            $table->string('extension')->nullable();
            $table->mediumText('content')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
