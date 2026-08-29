<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 191)->nullable()->change();
            $table->string('role', 30)->default('pengurus')->after('email'); // superadmin, pengurus, security
            $table->string('phone', 30)->nullable()->unique()->after('role');
            $table->unsignedSmallInteger('annual_leave_quota')->default(12)->after('phone');
            $table->boolean('is_active')->default(true)->after('annual_leave_quota');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropColumn(['role', 'phone', 'annual_leave_quota', 'is_active']);
            $table->string('email', 191)->nullable(false)->change();
        });
    }
};
