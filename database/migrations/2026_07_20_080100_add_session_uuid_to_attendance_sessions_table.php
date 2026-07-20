<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->uuid('session_uuid')->nullable()->after('attendance_session_id');
        });

        DB::table('attendance_sessions')
            ->select('attendance_session_id')
            ->orderBy('attendance_session_id')
            ->each(function (object $session): void {
                DB::table('attendance_sessions')
                    ->where('attendance_session_id', $session->attendance_session_id)
                    ->update(['session_uuid' => (string) Str::uuid()]);
            });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->uuid('session_uuid')->nullable(false)->change();
            $table->unique('session_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropUnique(['session_uuid']);
            $table->dropColumn('session_uuid');
        });
    }
};
