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
        Schema::table('shop_ingredients', function (Blueprint $table) {
            $table->decimal('price_small', 10, 2)->nullable();
            $table->decimal('price_medium', 10, 2)->nullable();
            $table->decimal('price_large', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_ingredients', function (Blueprint $table) {
            $table->decimal('price_small', 10, 2)->nullable();
            $table->decimal('price_medium', 10, 2)->nullable();
            $table->decimal('price_large', 10, 2)->nullable();
        });
    }
};
