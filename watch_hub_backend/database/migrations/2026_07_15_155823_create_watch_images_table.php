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
        Schema::create('watch_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('watch_id')->constrained()->cascadeOnDelete();
            $table->string('image_url', 500);
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->unsigned()->default(0);
            $table->timestamps();

            $table->index(['watch_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_images');
    }
};
