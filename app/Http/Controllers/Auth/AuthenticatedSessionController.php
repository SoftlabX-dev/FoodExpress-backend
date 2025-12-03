<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
 public function store(LoginRequest $request): JsonResponse
{
    $request->authenticate();

    if ($request->hasSession()) {
        $request->session()->regenerate();
    }

    return response()->json([
        'message' => 'Vous êtes bien connecté',
    ], 200);
}
 
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json(['message'=>'vous etes bien deconnecte'],201);
    }
}
