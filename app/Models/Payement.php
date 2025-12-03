<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'payment_method',
        'status',
        'transaction_id',
        'amount'
    ];

    // Chaque paiement appartient à une commande
    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
}
