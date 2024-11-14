<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'shop_ingredients';

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_visible' => 'boolean',
        'for_pizza' => 'boolean',
    ];

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Speciality::class, 'ingredient_specialty', 'shop_ingredient_id', 'shop_specialty_id');
    }
}
