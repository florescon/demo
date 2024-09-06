<?php

namespace App\Models\Shop;

use App\Models\Address;
use App\Models\Shop\Element;
use App\Models\Shop\Speciality;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
    ];

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Speciality::class, 'ingredient_specialty', 'shop_ingredient_id', 'shop_specialty_id');
    }
}
