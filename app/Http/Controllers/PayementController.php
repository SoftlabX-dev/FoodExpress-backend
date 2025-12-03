<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, $commandeId)
    {
        $commande = Commande::findOrFail($commandeId);

        // Si un paiement existe déjà → One-to-one
        if ($commande->payment) {
            return response()->json([
                'message' => 'Payment already exists for this order.'
            ], 400);
        }

        $payment = Payment::create([
            'commande_id' => $commandeId,
            'statut' => 'pending',
            'transaction_id' => $request->transaction_id,
            'amount' => $commande->prix_total
        ]);

        return response()->json([
            'message' => 'Payment created successfully.',
            'payment' => $payment
        ], 201);
    }
}
