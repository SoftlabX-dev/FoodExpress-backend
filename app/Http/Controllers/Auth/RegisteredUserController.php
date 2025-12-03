<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;


class RegisteredUserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
         $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
             //ici 'unique:..' recoit le nom table
            'password' => ['required', 'confirmed', Password::defaults()],
             //doit remplir le champ password_confirmation important
        ]);
        //password securise -Par défaut, ça impose :

// Minimum 8 caractères

// Doit contenir au moins une lettre majuscule (A-Z)

// Doit contenir au moins une lettre minuscule (a-z)

// Doit contenir au moins un chiffre (0-9)

// Peut contenir un caractère spécial

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client',
        ]);
  Auth::login($user);

       return response()->json([
  'message' => 'Compte créé avec succès',
  'user' => $user
], 201);

    }
}
