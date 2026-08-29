<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            // ULID, bukan auto-increment: id ini muncul di URL detail yang dikirim ke
            // grup Telegram, jadi tidak boleh bisa ditebak/di-enumerasi.
            $table->ulid('id')->primary();

            $table->string('name', 120);
            $table->string('phone', 30)->nullable();
            $table->string('host_name', 120)->nullable();
            $table->text('purpose');

            // Path relatif di disk "local" (storage/app/private). Nullable karena
            // file-nya dihapus command retensi setelah masa simpan habis.
            $table->string('ktp_path')->nullable();
            $table->string('selfie_path')->nullable();
            $table->timestamp('photos_purged_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();

            $table->timestamps();

            $table->index('created_at');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
