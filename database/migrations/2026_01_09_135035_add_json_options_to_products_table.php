<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // JSON arrays
            $table->json('storage_option_ids')->nullable()->after('os');
            $table->json('ram_option_ids')->nullable()->after('storage_option_ids');
            $table->json('color_ids')->nullable()->after('ram_option_ids');
        });

        /**
         * Backfill from legacy single columns if they exist:
         * - storage_option_id -> storage_option_ids
         * - ram_option_id -> ram_option_ids
         */
        if (Schema::hasColumn('products', 'storage_option_id')) {
            DB::statement("
                UPDATE products
                SET storage_option_ids = JSON_ARRAY(storage_option_id)
                WHERE storage_option_id IS NOT NULL
                  AND (storage_option_ids IS NULL OR JSON_LENGTH(storage_option_ids) = 0)
            ");
        }

        if (Schema::hasColumn('products', 'ram_option_id')) {
            DB::statement("
                UPDATE products
                SET ram_option_ids = JSON_ARRAY(ram_option_id)
                WHERE ram_option_id IS NOT NULL
                  AND (ram_option_ids IS NULL OR JSON_LENGTH(ram_option_ids) = 0)
            ");
        }

        /**
         * Backfill colors from pivot table (if you have it)
         * color_option_product: product_id, color_option_id
         */
        if (Schema::hasTable('color_option_product')) {
            $products = DB::table('products')->select('id')->get();

            foreach ($products as $p) {
                $ids = DB::table('color_option_product')
                    ->where('product_id', $p->id)
                    ->pluck('color_option_id')
                    ->map(fn ($v) => (int) $v)
                    ->values()
                    ->toArray();

                if (!empty($ids)) {
                    DB::table('products')->where('id', $p->id)->update([
                        'color_ids' => json_encode($ids),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['storage_option_ids', 'ram_option_ids', 'color_ids']);
        });
    }
};
