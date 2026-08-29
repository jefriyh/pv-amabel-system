<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_attendances', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('previous_security_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 30)->default('masuk'); // masuk, keluar, patroli
            $table->date('attendance_date');
            $table->string('day_name', 30); // Senin, Selasa, etc.
            $table->time('attendance_time');
            $table->time('start_time')->nullable(); // Jam mulai tugas
            $table->time('end_time')->nullable(); // Jam selesai tugas (default 12 jam kemudian)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_address', 255)->nullable();
            $table->string('selfie_path');
            $table->string('status', 30)->default('hadir'); // hadir, tepat_waktu, terlambat
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('attendance_date');
            $table->index(['user_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_attendances');
    }
};
