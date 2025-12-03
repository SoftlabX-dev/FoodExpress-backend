<?php

namespace App\Http\Controllers;

use App\Models\User;
 
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
class UserController extends Controller
{public function store(Request $request)
{
    $validated = $request->validate([
        'name'              => 'required|string|max:255',
        'email'             => 'required|email|unique:users,email',
        'role'              => 'required|string',
        'password'          => 'required|string|min:6',
        'phone'             => 'nullable|string|max:20',
        'adress'          =>'nullable|string'
    ]);

    $user = User::create([
        'name'              => $validated['name'],
        'email'             => $validated['email'],
        'role'              => $validated['role'],
        'password'          => Hash::make($validated['password']), 
        'phone'             => $validated['phone'] ?? null,
        'email_verified_at' => null,
                'adress'      => $validated['adress'],

    ]);

    return response()->json([
        'message' => 'Utilisateur ajouté avec succès',
        'user'    => $user,
    ], 201);
}

   



    public function updatename(Request $request,$id){
          $user = User::findorFail($id);
          $request->validate([
            'name' => ['required', 'string', 'max:255'],
          ]);
          $user->update(['name' => $request->name]);
       
        return response()->json([
            'message' => 'Nom mis à jour avec succès',
            'user' => $user
        ]);   
     }
        public function updateemail(Request $request,$id){
          $user = User::findorFail($id);
          $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
          ]);
          $user->update(['email' => $request->email]);
       
        return response()->json([
            'message' => 'email mis à jour avec succès',
            'user' => $user
        ]);   
     }
        public function updatepassword(Request $request){ 
          $request->validate([
            'password' => ['required', 'confirmed',Password::defaults()],
          ]);
          $user = auth()->user();
         
          $user->update(['password' => Hash::make($request->password)]);
       
        return response()->json([
            'message' => 'password mis à jour avec succès',
            'user' => $user
        ]);   
     }

     public function updatePhone(Request $request){
    $request->validate([
    'phone' => ['required', 'numeric', 'digits_between:8,15'],  
    ],
    [
      'phone.numeric'=>'Le numéro doit contenir seulement des chiffres .',
    ]
  );
    
    $user = auth()->user();
  
    $user->update([
        'phone' => $request->phone
    ]);

    return response()->json([
        'message' => 'Phone updated successfully',
        'user' => $user
    ]);
}

public function allusers()
{
    $users = User::with([
        'commandes',
        'ratings.plat',
    ])->get();

    $result = $users->map(function ($user) {

        // ========== CALCULS ==========
        $totalOrders = $user->commandes->count();
        $totalSpent  = $user->commandes->sum('prix_total');

        $lastOrder = optional(
            $user->commandes->sortByDesc('created_at')->first()
        )->created_at;

        $avgRating = round($user->ratings->avg('rating') ?? 0, 1);

        $favoriteItems = $user->ratings
            ->where('rating', '>=', 4)
            ->pluck('plat.nom')
            ->unique()
            ->values();

        // ========== TIER AUTOMATIQUE ==========
        $tier = "bronze";
        if ($totalSpent > 3000)  $tier = "gold";
        else if ($totalSpent > 1500) $tier = "silver";

        // ========== STATUS AUTOMATIQUE ==========
        // Active = a fait au moins 1 commande dans les 90 derniers jours
        $status = "inactive";
        if ($lastOrder && $lastOrder->gt(now()->subDays(90))) {
            $status = "active";
        }

        // ========== NOTES ==========
        $notes = $user->notes ?? "No notes"; // si tu as une colonne "notes"

        // ========== ADDRESS ==========
        $address = $user->email 
           ;
        return [
            "id"            => $user->id,
            "name"          => $user->name,
            "email"         => $user->email,
            "phone"         => $user->phone,
            "address"       => $address,

            "joinDate"      => optional($user->created_at)->format('Y-m-d'),

            "totalOrders"   => $totalOrders,
            "totalSpent"    => $totalSpent,

            "status"        => $status,
            "tier"          => $tier,

            "avatar"        => "https://i.pravatar.cc/150?u=user_" . $user->id,

            "lastOrder"     => optional($lastOrder)->format('Y-m-d'),
            "favoriteItems" => $favoriteItems,

            "rating"        => $avgRating,

            "notes"         => $notes,

            "reviews"       => $user->ratings->map(function ($rate) {
                return [
                    'plat'     => $rate->plat->nom ?? "Deleted item",
                    'rating'   => $rate->rating,
                    'feedback' => $rate->feedback,
                    'date'     => optional($rate->created_at)->format('Y-m-d')
                ];
            }),
        ];
    });

    return response()->json($result);
}





}
