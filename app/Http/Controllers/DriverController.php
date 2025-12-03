<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Commande;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    // Dashboard des livreurs
    public function dashboard()
    {
        try {
            $totalDrivers = Driver::count();
            $activeDrivers = Driver::where('statut', 'active')->count();
            $onDeliveryDrivers = Driver::where('statut', 'on_delivery')->count();
            $offlineDrivers = Driver::where('statut', 'offline')->count();
            $avgRating = Driver::avg('rating');
            $totalDeliveries = Driver::sum('total_deliveries');

            $drivers = Driver::withCount(['activeDeliveries'])
                ->with(['ratings' => function ($query) {
                    $query->latest()->limit(5);
                }])
                ->get()
                ->map(function ($driver) {
                    return [
                        'id' => $driver->id,
                        'name' => $driver->user->name,
                        'initials' => $driver->user->initials,
                        'phone' => $driver->user->phone,
                        'email' => $driver->user->email,
                        'vehicle' => $driver->vehicle_type,
                        'vehicle_plate' => $driver->vehicle_plate,
                        'status' => $driver->statut,
                        'rating' => (float) $driver->rating,
                        'total_deliveries' => $driver->total_deliveries,
                        'completed_deliveries' => $driver->completed_deliveries,
                        'current_deliveries' => $driver->active_deliveries_count,
                        'total_earnings' => (float) $driver->total_earnings,
                        'is_available' => $driver->is_available,
                        'ratings_count' => $driver->ratings->count(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'total_drivers' => $totalDrivers,
                        'active' => $activeDrivers,
                        'on_delivery' => $onDeliveryDrivers,
                        'offline' => $offlineDrivers,
                        'avg_rating' => round($avgRating, 1),
                        'total_deliveries' => $totalDeliveries,
                    ],
                    'drivers' => $drivers,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Liste tous les livreurs
    public function index(Request $request)
    {
        try {
            $query = Driver::query();

            // Filtres
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('available')) {
                $query->where('is_available', $request->available);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('vehicle_plate', 'like', "%{$search}%");
                });
            }

            $drivers = $query->withCount('activeDeliveries')->get();

            return response()->json([
                'success' => true,
                'drivers' => $drivers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des livreurs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        // USER
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'phone' => 'required|string|unique:users,phone',

        // DRIVER PROFILE
        'vehicle_type' => 'required',
        'vehicle_plate' => 'nullable|string|max:20',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        DB::beginTransaction();

        // 1 Créer le compte utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'driver',
            'phone' => $request->phone,
        ]);

        // 2️ Créer le profil du livreur lié à user_id
        $driver = Driver::create([
            'user_id' => $user->id,
            'vehicle_type' => $request->vehicle_type,
            'vehicle_plate' => $request->vehicle_plate,
            'available' => true,
            'statut' => 'active',
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Livreur créé avec succès',
            'user' => $user,
            'driver' => $driver,
        ], 201);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création du livreur',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    // Afficher un livreur spécifique
    public function show($id)
    {
        try {
            $driver = Driver::with(['commandes', 'ratings.user'])
                ->withCount('activeDeliveries')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'driver' => $driver,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Livreur non trouvé',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    // Mettre à jour un livreur
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'phone' => 'string|unique:livreurs,phone,' . $id,
            'email' => 'nullable|email|unique:livreurs,email,' . $id,
            'vehicle_type' => 'in:Motorcycle,Scooter,Car',
            'vehicle_plate' => 'nullable|string|max:20',
            'status' => 'in:active,on_delivery,offline',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $driver = Driver::findOrFail($id);
            $driver->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Livreur mis à jour avec succès',
                'driver' => $driver,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Supprimer un livreur
    public function destroy($id)
    {
        try {
            $driver = Driver::findOrFail($id);
            $driver->delete();

            return response()->json([
                'success' => true,
                'message' => 'Livreur supprimé avec succès',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Changer le statut d'un livreur
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'statut' => 'required|in:active,on_delivery,offline',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $driver = Driver::findOrFail($id);
            $driver->update(['statut' => $request->statut]);

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'driver' => $driver,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Assigner un livreur à une commande
    public function assignToOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:livreurs,id',
            'commande_id' => 'required|exists:commandes,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $driver = Driver::findOrFail($request->driver_id);
            $commande = Commande::findOrFail($request->commande_id);

          

            // Assigner la commande
            $commande->update([
                'driver_id' => $driver->id,
                'statut' => 'on_delivery',
            ]);

            // Mettre à jour le livreur
            $driver->incrementDeliveries();
            $driver->update(['statut' => 'on_delivery']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Livreur assigné avec succès',
                'commande' => $commande->load('livreur'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Livreurs disponibles pour une commande
 public function getAvailableDrivers(Request $request)
    {
        try {
            $commandeId = $request->input('commande_id');
            $currentDriverId = null;

            // Si une commande est spécifiée, récupérer son livreur actuel
            if ($commandeId) {
                $commande = Commande::find($commandeId);
                $currentDriverId = $commande ? $commande->driver_id : null;
            }

            // Récupérer les livreurs disponibles en excluant le livreur actuel
            $query = Driver::where('available', true)
                ->whereIn('statut', ['active', 'on_delivery'])
                ->withCount([
                    'commandes as active_deliveries_count' => function ($query) {
                        $query->whereIn('statut', ['assigned', 'picked_up', 'in_transit']);
                    }
                ])
                ->with('user:id,name,email,phone');

            // Exclure le livreur actuellement assigné
            if ($currentDriverId) {
                $query->where('id', '!=', $currentDriverId);
            }

            $drivers = $query
                ->orderBy('active_deliveries_count', 'asc')
                ->orderBy('rating', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'drivers' => $drivers,
                'current_driver_id' => $currentDriverId,
                'total' => $drivers->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des livreurs disponibles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

public function getAllDeliveries() {
    $driver = Driver::where('user_id', auth()->user()->id)->first();

    if (!$driver) {
        return response()->json([
            'success' => false,
            'message' => 'Livreur non trouvé',
        ], 404);
    }

    $commandes = Commande::with(['user','plats','adresseLivraison','livreur'])
        ->where('driver_id', $driver->id)
        ->orderBy('date_commande', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'commandes' => $commandes
    ], 200);
}



}