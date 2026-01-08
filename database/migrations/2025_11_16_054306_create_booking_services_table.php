<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();

            // Again: plain integer IDs, no foreign key constraints
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('service_variant_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();

            $table->string('label');
            $table->integer('duration_minutes');
            $table->integer('extra_minutes')->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->decimal('price', 10, 2);           // base price
            $table->string('discount_type', 20)->default('none'); // none|percent|amount
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);     // after discount

            $table->string('color_code', 20)->nullable();

            $table->timestamps();

            // indexes only
            $table->index('booking_id');
            $table->index('service_id');
            $table->index('service_variant_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};
