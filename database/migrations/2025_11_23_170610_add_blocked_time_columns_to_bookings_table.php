<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('block_type')->nullable()->after('status');
            $table->string('frequency')->nullable()->after('block_type');
            $table->text('description')->nullable()->after('frequency');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['block_type', 'frequency', 'description']);
        });
    }
};
