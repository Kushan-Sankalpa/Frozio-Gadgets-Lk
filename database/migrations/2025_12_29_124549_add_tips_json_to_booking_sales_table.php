<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('booking_sales', function (Blueprint $table) {
            $table->json('tips_json')->nullable()->after('tip_staff_id');
        });
    }

    public function down(): void
    {
        Schema::table('booking_sales', function (Blueprint $table) {
            $table->dropColumn('tips_json');
        });
    }
};
