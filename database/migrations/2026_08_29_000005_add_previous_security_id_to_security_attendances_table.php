<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('security_attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('security_attendances', 'previous_security_id')) {
                $table->foreignId('previous_security_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('security_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('security_attendances', 'previous_security_id')) {
                $table->dropForeign(['previous_security_id']);
                $table->dropColumn('previous_security_id');
            }
        });
    }
};
