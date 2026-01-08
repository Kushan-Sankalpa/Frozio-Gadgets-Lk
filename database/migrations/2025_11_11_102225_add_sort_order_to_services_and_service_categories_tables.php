<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'sort_order')) {
                $table->integer('sort_order')->default(0)->index()->after('service_category_id');
            }
        });

        Schema::table('service_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('service_categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->index()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });

        Schema::table('service_categories', function (Blueprint $table) {
            if (Schema::hasColumn('service_categories', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
