<?php

namespace App\Models;

use App\Http\Controllers\DriverRatingsController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{

    use HasFactory;
    protected $table = 'livreurs';

    protected $fillable = [
        'user_id',
        'phone',
        'vehicle_type',
        'vehicle_plate',
        'available',
        'statut',
        'current_location',
        'rating',
        'total_deliveries',
        'completed_deliveries',
        'total_earnings',
        'profile_image',
    ];
    protected $casts = [
        'rating' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'is_available' => 'boolean',
    ];
    // Relations
    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }

    public function ratings()
    {
        return $this->hasMany(DriverRating::class);
    }

    public function activeDeliveries()
    {
        return $this->hasMany(Commande::class)
            ->whereIn('statut', ['preparing', 'pending', 'on_delivery']);
    }

    // Méthodes 

    public function updateRating()
    {
        $avgRating = $this->ratings()->avg('rating');
        $this->update(['rating' => round($avgRating, 2)]);
    }

    public function incrementDeliveries()
    {
        $this->increment('total_deliveries');
    }

    public function completeDelivery($amount)
    {
        $this->increment('completed_deliveries');
        $this->decrement('current_deliveries');
        $this->increment('total_earnings', $amount);
    }
    // Relation avec le User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
