<?php

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
        Schema::create('money_log_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained();
            $table->foreignId('round_id')->constrained();
            $table->integer('amount');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_log_entries');
    }
};
