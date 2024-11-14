<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Element extends Model
{
    use HasFactory;

    protected $table = 'shop_elements';

    protected $guarded = [];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /** @return BelongsTo<Ingredient,self> */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /** @return MorphTo<Model,self> */
    public function elementable(): MorphTo
    {
        return $this->morphTo();
    }
}
