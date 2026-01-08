<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedInteger('current_points')->default(0);

            $table->unsignedInteger('lifetime_points')->default(0);

            $table->unsignedBigInteger('loyalty_tier_id')
                ->nullable()
                ->after('lifetime_points');

            $table->foreign('loyalty_tier_id')
                ->references('id')
                ->on('loyalty_tiers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['loyalty_tier_id']);
            $table->dropColumn(['current_points', 'lifetime_points', 'loyalty_tier_id']);
        });
    }
};
