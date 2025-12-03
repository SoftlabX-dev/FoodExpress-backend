<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    // ✅ Status constants matching the required workflow
    const STATUS_PREPARING = 'preparing';      // Default when order is created
    const STATUS_PENDING = 'pending';          // Admin sets it to pending
    const STATUS_ON_DELIVERY = 'on_delivery';  // When assigned to driver
    const STATUS_COMPLETED = 'completed';      // Driver completes delivery
    const STATUS_CANCELLED = 'cancelled';      // Can be cancelled anytime

    const STATUTS = [
        self::STATUS_PREPARING,
        self::STATUS_PENDING,
        self::STATUS_ON_DELIVERY,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED
    ];

    protected $fillable = [
        'user_id',
        'adresse_livraison_id',
        'prix_total',
        'paymentMethod',
        'statut',
        'date_commande',
        'priority',
        'driver_id',
    ];


    public function plats()
    {
        return $this->belongsToMany(Plat::class, 'commande_plat')->withPivot('quantite');
    }

    //Un utilisateur (User) peut passer plusieurs commandes.
//Une commande (Commande) appartient à un seul utilisateur.
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function AdresseLivraison()
    {
        return $this->belongsTo(AdresseLivraison::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    public function livreur()
    {
        // Supposons que votre clé étrangère est 'livreur_id' et le modèle cible est 'App\Models\Livreur'
        return $this->belongsTo(Driver::class, 'driver_id');
    }




}
