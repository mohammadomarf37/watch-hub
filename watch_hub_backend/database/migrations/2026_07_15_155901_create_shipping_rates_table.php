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
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('shipping_method_id')->constrained()->cascadeOnDelete();
            $table->string('country_code', 2);
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->decimal('rate', 10, 2);
            $table->boolean('is_free_shipping')->default(false);
            $table->timestamps();

            $table->index('country_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
