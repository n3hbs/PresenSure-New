<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // These fields are already part of the create_attendance_sessions
        // migration. Keeping this migration empty prevents duplicate columns.
    }

    public function down(): void
    {
        // Nothing was added in up(), so there is nothing to remove.
    }
};
