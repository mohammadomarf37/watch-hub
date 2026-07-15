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
        Schema::create('watch_variants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('watch_id')->constrained()->cascadeOnDelete();
            $table->string('color')->nullable();
            $table->char('color_hex', 7)->nullable();
            $table->string('size')->nullable();
            $table->integer('stock')->unsigned()->default(0);
            $table->decimal('additional_price', 10, 2)->default(0.00);
            $table->string('sku')->unique();
            $table->timestamps();

            $table->index(['watch_id', 'stock']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_variants');
    }
};
