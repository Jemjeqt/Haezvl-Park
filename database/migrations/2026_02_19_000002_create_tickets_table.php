<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->string('plate_number');
            $table->enum('vehicle_type', ['motor', 'mobil', 'truk']);
            $table->enum('status', ['IN', 'PAID', 'OUT'])->default('IN');
            $table->timestamp('entry_time');
            $table->timestamp('paid_time')->nullable();
            $table->timestamp('exit_time')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('plate_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
