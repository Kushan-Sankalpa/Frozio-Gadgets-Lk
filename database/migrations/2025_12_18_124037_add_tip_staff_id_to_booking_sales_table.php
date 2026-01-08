<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_sales', function (Blueprint $table) {
            $table->unsignedBigInteger('tip_staff_id')->nullable()->after('tip_amount');

        });
    }

    public function down(): void
    {
        Schema::table('booking_sales', function (Blueprint $table) {
            $table->dropForeign(['tip_staff_id']);
            $table->dropColumn('tip_staff_id');
        });
    }
};
