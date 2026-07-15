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
        Schema::create('watch_specifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('watch_id')->constrained()->cascadeOnDelete();
            $table->string('spec_key');
            $table->string('spec_value');
            $table->timestamps();

            $table->unique(['watch_id', 'spec_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_specifications');
    }
};
