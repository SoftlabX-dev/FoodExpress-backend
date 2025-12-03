<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rating;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'plat_id' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string'
        ]);

        Rating::create([
            'user_id' => auth()->id(),
            'plat_id' => $request->plat_id,
            'rating' => $request->rating,
            'feedback' => $request->feedback
        ]);

        return response()->json(['message' => 'Rating ajouté avec succès']);
    }

    public function MoyenneRating($plat_id)
    {
        $avg = Rating::where('plat_id', $plat_id)->avg('rating');
        $count = Rating::where('plat_id', $plat_id)->count();

        return response()->json([
            'average' => round($avg, 1),
            'count' => $count
        ]);
    }
}
