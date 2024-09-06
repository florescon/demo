<?php

use App\Models\Shop\Ingredient;
use App\Models\Shop\Speciality;
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
        Schema::create('shop_elements', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Speciality::class)->nullable()->constrained('shop_ingredients')->cascadeOnDelete();
            $table->foreignIdFor(Ingredient::class)->nullable()->constrained('shop_specialties')->cascadeOnDelete();
            $table->text('title')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_visible')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_elements');
    }
};
