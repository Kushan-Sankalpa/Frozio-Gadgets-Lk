<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_queues', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();

            $table->string('channel')->default('sms');
            $table->string('phone')->index();
            $table->text('message');


            $table->string('status')->default('pending');

            $table->unsignedInteger('gateway_status_code')->nullable();
            $table->json('gateway_response')->nullable();
            $table->text('error_message')->nullable();

            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();

            $table->timestamps();


        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_queues');
    }
};
