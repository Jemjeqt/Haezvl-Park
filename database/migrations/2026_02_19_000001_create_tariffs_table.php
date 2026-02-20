<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->enum('vehicle_type', ['motor', 'mobil', 'truk'])->unique();
            $table->integer('hourly_rate');
            $table->integer('daily_max');
            $table->integer('grace_period_minutes')->default(15);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
