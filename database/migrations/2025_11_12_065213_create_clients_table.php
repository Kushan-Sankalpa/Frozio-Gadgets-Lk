<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('clients', function (Blueprint $table) {
        $table->id();

        $table->string('first_name');
        $table->string('last_name');
        $table->string('email')->nullable();                 
        $table->string('phone')->nullable();                 
        $table->string('phone_code', 10)->nullable();        
        $table->char('birthday_daymonth', 5)->nullable();   
        $table->unsignedSmallInteger('birthday_year')->nullable();

        $table->string('gender', 32)->nullable();

        $table->foreignId('country_id')->nullable()
              ->constrained('countries')->nullOnDelete();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
