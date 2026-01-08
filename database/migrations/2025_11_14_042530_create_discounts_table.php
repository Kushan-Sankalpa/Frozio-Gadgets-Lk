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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            $table->enum('discount_type', ['fixed', 'percentage']);
            $table->decimal('discount_amount', 10, 2);

            $table->integer('priority')->default(0);

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
