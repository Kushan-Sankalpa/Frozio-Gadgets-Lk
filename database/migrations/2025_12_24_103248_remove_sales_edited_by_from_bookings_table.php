<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Find the real FK name for sales_edited_by OR sale_edited_by
        $fk = DB::selectOne("
            SELECT CONSTRAINT_NAME AS name, COLUMN_NAME AS col
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'bookings'
              AND REFERENCED_TABLE_NAME IS NOT NULL
              AND COLUMN_NAME IN ('sales_edited_by','sale_edited_by')
            LIMIT 1
        ");

        // 2) Drop FK using the REAL name (if exists)
        if ($fk && !empty($fk->name)) {
            DB::statement("ALTER TABLE `bookings` DROP FOREIGN KEY `{$fk->name}`");
        }

        // 3) Drop the column (try both safely)
        if (Schema::hasColumn('bookings', 'sales_edited_by')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('sales_edited_by');
            });
        }

        if (Schema::hasColumn('bookings', 'sale_edited_by')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('sale_edited_by');
            });
        }
    }

    public function down(): void
    {
        // optional: restore if you want (leave empty if you don't care)
    }
};
