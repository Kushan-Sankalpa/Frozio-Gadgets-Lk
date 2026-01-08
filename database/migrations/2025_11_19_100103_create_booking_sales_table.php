<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_sales', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->string('payment_method', 50)->nullable(); // cash / card / split / other

            $table->decimal('base_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('tip_amount', 10, 2)->default(0);
            $table->decimal('total_with_tip', 10, 2)->default(0);

            $table->decimal('total_paid', 10, 2)->default(0);
            $table->decimal('remaining', 10, 2)->default(0);

            // store full details as JSON so you can inspect later
            $table->json('payments_json')->nullable();   // list of lines: [{method, amount}]
            $table->json('services_json')->nullable();   // cart/services snapshot

            $table->timestamps();

            $table->foreign('booking_id')
                ->references('id')->on('bookings')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_sales');
    }
};
