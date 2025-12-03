<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdresseLivraison extends Model
{
    use HasFactory;

    protected $table = 'adresses_livraison';

   protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'street_address',
        'delivery_instructions',
    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
 public function commandes()
    {
        return $this->hasMany(Commande::class, 'adresse_livraison_id');
    }
}
