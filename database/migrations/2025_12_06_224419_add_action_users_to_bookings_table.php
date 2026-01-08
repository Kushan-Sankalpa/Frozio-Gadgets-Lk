<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('placed_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->foreignId('approved_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->foreignId('rejected_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->foreignId('cancelled_by')->nullable()
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('placed_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropConstrainedForeignId('cancelled_by');
        });
    }
};
