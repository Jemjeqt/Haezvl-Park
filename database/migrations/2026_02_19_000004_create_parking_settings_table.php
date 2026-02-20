<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parking_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps();
        });

        // Default settings
        \Illuminate\Support\Facades\DB::table('parking_settings')->insert([
            ['key' => 'capacity_motor', 'value' => '50', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'capacity_mobil', 'value' => '100', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'capacity_truk', 'value' => '20', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'expire_minutes', 'value' => '15', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_settings');
    }
};
