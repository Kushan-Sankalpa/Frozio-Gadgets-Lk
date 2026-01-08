<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $t) {
            $t->string('address_type', 16)->nullable();      
            $t->string('address', 255)->nullable();        
            $t->string('district', 120)->nullable();
            $t->string('city', 120)->nullable();
            $t->string('postcode', 40)->nullable();

          
            $t->unsignedBigInteger('address_country_id')->nullable()->index();
            $t->string('address_country', 120)->nullable();

            
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $t) {
           
            $t->dropColumn([
                'address_type', 'address', 'district', 'city', 'postcode',
                'address_country_id', 'address_country',
            ]);
        });
    }
};
