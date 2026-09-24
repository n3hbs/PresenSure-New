<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add module_name to permissions for dynamic grouping
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('module_name')->nullable()->after('permission_name');
        });

        // 2. Add is_system_admin to roles to replace hardcoded 'administrator' string checks
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_system_admin')->default(false)->after('role_name');
        });

        // 3. Ensure existing administrator role has is_system_admin = true
        DB::table('roles')
            ->where('role_name', 'administrator')
            ->update(['is_system_admin' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('module_name');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('is_system_admin');
        });
    }
};
