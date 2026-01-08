<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notification_queues', function (Blueprint $table) {
            $table->string('type', 60)->nullable()->after('channel');
            $table->index(['booking_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('notification_queues', function (Blueprint $table) {
            $table->dropIndex(['booking_id', 'type', 'status']);
            $table->dropColumn('type');
        });
    }
};
