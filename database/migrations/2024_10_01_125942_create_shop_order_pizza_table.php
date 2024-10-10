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
        Schema::create('shop_order_pizzas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('size')->nullable();
            $table->string('choose')->nullable();
            $table->string('properties')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_order_pizza');
    }
};
