<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Just plain integer columns, no FK constraints
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();

            $table->date('date')->nullable();       // appointment date
            $table->timestamp('starts_at')->nullable();// first service start
            $table->timestamp('ends_at')->nullable(); // last service end

            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('status', 50)->default('scheduled');
            $table->text('notes')->nullable();

            $table->timestamps();

            // optional indexes for speed
            $table->index('client_id');
            $table->index('staff_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
