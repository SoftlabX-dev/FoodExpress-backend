<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * Store payment information for a commande
     */
    public function store(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        $commande = Commande::findOrFail($id);

        $payment = Payment::create([
            'commande_id' => $commande->id,
            'payment_method' => $validated['payment_method'],
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'transaction_id' => $validated['transaction_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Payment recorded successfully',
            'payment' => $payment,
        ], 201);
    }
}
