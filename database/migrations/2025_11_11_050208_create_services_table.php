<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_category_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('price_type', ['fixed', 'from', 'free'])->default('fixed');
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('duration_minutes')->default(60); // store minutes
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'service_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
