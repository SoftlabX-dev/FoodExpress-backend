<?php

namespace App\Http\Controllers;
use App\Models\AdresseLivraison;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdresseLivraisonController extends Controller
{
 public function store(Request $request)
{
    $validated = $request->validate([
        'full_name' => [
            'required',
            'string',
            'max:255',
            'regex:/^[a-zA-ZÀ-ÿ ]{3,}$/'
        ],

        'phone' => [
            'required',
            'regex:/^[0-9]{6,15}$/'
        ],

        'street_address' => [
            'required',
            'string',
            'min:6',
            'max:255',
            'regex:/^[0-9a-zA-ZÀ-ÿ \-,]{6,}$/'
        ],
        'delivery_instructions' => 'nullable|string|max:255',
    ], [
        'full_name.regex' => 'Le nom doit contenir seulement des lettres et au moins 3 caractères.',
        'phone.regex' => 'Le numéro doit contenir seulement des chiffres .',
        'street_address.regex' => 'L’adresse doit être une adresse valide.',
    ]);

    // Vérifie si l'utilisateur est bien authentifié
    $user = Auth::user();
    if (!$user) {
        return response()->json(['error' => 'Utilisateur non authentifié'], 401);
    }

    $adresse = AdresseLivraison::create([
        'user_id' => $user->id,
        'full_name' => $validated['full_name'],
        'phone' => $validated['phone'],
        'street_address' => $validated['street_address'],
        'delivery_instructions' => $validated['delivery_instructions'] ?? null,
    ]);

    return response()->json($adresse, 201);
}

}