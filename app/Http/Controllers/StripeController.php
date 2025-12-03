<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
       
            // Configuration de Stripe
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Validation des données
            $request->validate([
                'amount' => 'required|integer|min:50', // Au moins 0.50 USD
                'livraison_id' => 'required|integer'
            ]);

            // Création du PaymentIntent
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount,
                'currency' => 'usd',
                'metadata' => [
                    'livraison_id' => $request->livraison_id
                ],
                // Ajout de l'automatic_payment_methods pour Stripe Elements
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'status' => 'success'
            ]);

        }
    
}