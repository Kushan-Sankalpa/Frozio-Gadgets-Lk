<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // If the table doesn't exist, create it with all the columns we need
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary(); // we seed explicit IDs from CSV
                $table->string('name');
                $table->string('code', 10); // dialing code as string (e.g. "94", "1", "6723")
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
            return;
        }

        // Otherwise, add any missing columns safely (no "after()" to avoid positional errors)
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('countries', 'code')) {
                $table->string('code', 10)->nullable();
            }
            if (!Schema::hasColumn('countries', 'status')) {
                $table->tinyInteger('status')->default(1);
            }
            if (!Schema::hasColumn('countries', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        // Only drop columns we added if they exist
        if (!Schema::hasTable('countries')) return;

        Schema::table('countries', function (Blueprint $table) {
            if (Schema::hasColumn('countries', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('countries', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('countries', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('countries', 'created_at')) {
                $table->dropTimestamps();
            }
        });
    }
};
