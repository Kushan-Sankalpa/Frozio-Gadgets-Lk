<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id')->index();

            $table->string('name')->nullable();      // Optional
            $table->text('description')->nullable(); // Optional
            $table->enum('price_type', ['fixed','from','free'])->default('fixed');
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->timestamps();
            $table->softDeletes();

      
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_variants');
    }
};
