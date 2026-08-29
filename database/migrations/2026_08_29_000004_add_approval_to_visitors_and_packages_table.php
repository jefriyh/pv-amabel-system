<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('status', 30)->default('approved')->after('purpose'); // pending, approved, rejected, checked_in
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
        });

        Schema::table('package_deliveries', function (Blueprint $table) {
            $table->string('status', 30)->default('dititipkan')->after('tracking_number'); // dititipkan, diterima_penghuni, dikembalikan
            $table->string('received_by', 120)->nullable()->after('status');
            $table->timestamp('received_at')->nullable()->after('received_by');
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status', 'approved_by', 'approved_at', 'approval_notes']);
        });

        Schema::table('package_deliveries', function (Blueprint $table) {
            $table->dropColumn(['status', 'received_by', 'received_at']);
        });
    }
};
