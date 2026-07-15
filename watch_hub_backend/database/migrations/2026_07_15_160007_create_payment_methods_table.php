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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('provider', ['stripe', 'paypal', 'razorpay', 'paystack']);
            $table->string('provider_customer_id');
            $table->string('payment_method_id');
            $table->char('last_four', 4)->nullable();
            $table->string('brand', 50)->nullable();
            $table->tinyInteger('exp_month')->unsigned()->nullable();
            $table->smallInteger('exp_year')->unsigned()->nullable();
            $table->boolean('is_default')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->unique(['user_id', 'provider_customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
