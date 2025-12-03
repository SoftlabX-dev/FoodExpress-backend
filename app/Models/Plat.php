<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plat extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description', 'prix', 'image','review_count','isAvailable',
        'isPopular',
        'isFeatured',
        'discount','category_id'];

protected static function booted()
{
    // Quand un plat est créé
    static::created(function ($plat) {
        if ($plat->category_id) {
            $plat->category->increment('total_products');
        }
    });

    // Quand un plat est supprimé
    static::deleted(function ($plat) {
        if ($plat->category_id) {
            $plat->category->decrement('total_products');
        }
    });

    // Quand un plat change de catégorie
    static::updated(function ($plat) {
        if ($plat->isDirty('category_id')) {
            $oldCategoryId = $plat->getOriginal('category_id');
            $newCategoryId = $plat->category_id;

            if ($oldCategoryId) {
                Category::where('id', $oldCategoryId)->decrement('total_products');
            }

            if ($newCategoryId) {
                Category::where('id', $newCategoryId)->increment('total_products');
            }
        }
    });
}


  public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function commandes()
    {
        return $this->belongsToMany(Commande::class, 'commande_plat')->withPivot('quantite');
    }
   
}
