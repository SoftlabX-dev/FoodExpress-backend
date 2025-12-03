<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Récupère les données des cartes de statistiques (KPIs).
     */
    public function getKpis()
    {
        // Définir les périodes pour la comparaison
        $currentDate = Carbon::now();
        $startDate = $currentDate->copy()->startOfDay();
        $prevStartDate = $currentDate->copy()->subDay()->startOfDay();

        // 1. Total Commandes & Active Commandes
        $totalCommandes = Commande::where('created_at', '>=', $startDate)->count();
        $prevTotalCommandes = Commande::where('created_at', '>=', $prevStartDate)
            ->where('created_at', '<', $startDate)->count();

        $activeCommandes = Commande::whereIn('statut', ['pending', 'preparing', 'on_delivery'])->count();

        // 2. Revenue Today
        $revenueToday = Commande::where('created_at', '>=', $startDate)
            ->where('statut', 'completed')
            ->sum('prix_total');

        $prevRevenueToday = Commande::where('created_at', '>=', $prevStartDate)
            ->where('created_at', '<', $startDate)
            ->where('statut', 'completed')
            ->sum('prix_total');

        // 3. New Customers
        $newCustomers = User::where('created_at', '>=', $startDate)
            ->where('role', 'client')
            ->count();

        // Fonction utilitaire pour calculer le pourcentage de croissance
        $calculateTrend = function ($current, $previous) {
            if ($previous == 0)
                return $current > 0 ? '+100%' : '0%';
            $percentage = (($current - $previous) / $previous) * 100;
            return ($percentage >= 0 ? '+' : '') . number_format($percentage, 1) . '%';
        };



        return response()->json([
            'total_Commandes' => [
                'value' => $totalCommandes,
                'trend' => $calculateTrend($totalCommandes, $prevTotalCommandes),
            ],
            'revenue_today' => [
                'value' => $revenueToday,
                'trend' => $calculateTrend($revenueToday, $prevRevenueToday),
            ],
            'active_Commandes' => [
                'value' => $activeCommandes,
            ],
            'new_customers' => [
                'value' => $newCustomers,
            ],
        ]);
    }


    /**
     * Récupère les données de revenus agrégées pour le graphique.
     * @param string $period (ex: 7days, 30days, 1year)
     */
    public function getRevenueTrends($period)
    {
        // Logique de base pour définir les dates de début et la granularité
        if ($period === '7days') {
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $groupFormat = '%a'; // Jour de la semaine (Mon, Tue, etc.)
        } elseif ($period === '30days') {
            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $groupFormat = '%d'; // Jour du mois (1, 2, 3, ...)
        } else { // 1year par défaut ou autre
            $startDate = Carbon::now()->subYear()->startOfDay();
            $groupFormat = '%b'; // Mois (Jan, Feb, Mar, ...)
        }

        $revenueData = Commande::selectRaw(
            'DATE_FORMAT(created_at, ?) as period_label, SUM(prix_total) as revenue_sum',
            [$groupFormat]
        )->where('created_at', '>=', $startDate)
            ->where('statut', 'completed')
            ->groupBy('period_label')
            ->orderBy('period_label')
            ->get();

        // Réponse JSON pour le frontend
        return response()->json([
            'labels' => $revenueData->pluck('period_label')->toArray(),
            'data' => $revenueData->pluck('revenue_sum')->toArray(),
        ]);

    }


    public function getOrderDistribution(string $period = 'today')
    {
        //  Déterminer la date de début selon la période
        $now = Carbon::now();
        $startDate = match ($period) {
            'today' => $now->startOfDay(),
            '7days' => $now->subDays(6)->startOfDay(),
            '30days' => $now->subDays(29)->startOfDay(),
            default => $now->startOfDay(),
        };

        // Récupérer les commandes filtrées par date
        $orders = Commande::where('created_at', '>=', $startDate)->get();

        $totalOrders = $orders->count();

        // Si pas de commandes, retourner vite fait
        if ($totalOrders === 0) {
            return response()->json([
                'total_orders' => 0,
                'completion_rate' => 0,
                'active_orders' => 0,
                'distribution' => [],
            ]);
        }

        //  Compter les commandes par statut
        $statutCounts = $orders->groupBy('statut')->map(fn($group) => $group->count());

        $distribution = [];
        $activeOrders = 0;
        $completedOrders = 0;

        foreach (Commande::STATUTS as $statut) {
            $count = $statutCounts[$statut] ?? 0;
            $distribution[] = [
                'statut' => $statut,
                'count' => $count,
                'percentage' => round(($count / $totalOrders) * 100, 1),
            ];

            if (in_array($statut, ['preparing', 'pending', 'on_delivery'])) {
                $activeOrders += $count;
            }
            if ($statut === 'completed') {
                $completedOrders = $count;
            }
        }

        $completionRate = round(($completedOrders / $totalOrders) * 100, 1);

        return response()->json([
            'total_orders' => $totalOrders,
            'completion_rate' => $completionRate,
            'active_orders' => $activeOrders,
            'distribution' => $distribution,
        ]);
    }


}