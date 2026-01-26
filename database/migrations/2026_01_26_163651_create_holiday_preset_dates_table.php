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
        Schema::create('holiday_preset_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('holiday_preset_id')->constrained()->cascadeOnDelete();
            $table->date('holiday_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_preset_dates');
    }
};
