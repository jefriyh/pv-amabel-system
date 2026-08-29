<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_deliveries', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('courier_name', 120);
            $table->string('courier_company', 60);
            $table->string('recipient_note', 160)->nullable();
            $table->string('tracking_number', 80)->nullable();

            // Foto paket di dalam kotak = bukti drop-off. Selfie kurir opsional.
            $table->string('photo_path')->nullable();
            $table->string('selfie_path')->nullable();
            $table->timestamp('photos_purged_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();

            $table->timestamps();

            $table->index('created_at');
            $table->index('courier_company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_deliveries');
    }
};
