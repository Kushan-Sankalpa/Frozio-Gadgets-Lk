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
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('auto_cancelled')->default(false)->after('status');
            $table->timestamp('auto_cancelled_at')->nullable()->after('auto_cancelled');
            $table->string('auto_cancel_reason')->nullable()->after('auto_cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['auto_cancelled', 'auto_cancelled_at', 'auto_cancel_reason']);
        });
    }
};
