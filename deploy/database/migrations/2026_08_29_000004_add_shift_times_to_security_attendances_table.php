<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('security_attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('security_attendances', 'start_time')) {
                $table->time('start_time')->nullable()->after('attendance_time');
            }
            if (! Schema::hasColumn('security_attendances', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('security_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('security_attendances', 'start_time')) {
                $table->dropColumn('start_time');
            }
            if (Schema::hasColumn('security_attendances', 'end_time')) {
                $table->dropColumn('end_time');
            }
        });
    }
};
