<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsBlockedToClientsTable extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('email');
            }
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'is_blocked')) {
                $table->dropColumn('is_blocked');
            }
        });
    }
}