<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'commande_id',
        'user_id',
        'rating',
        'comment',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}