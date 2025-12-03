<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticController extends Controller
{
    // ------------------ STATS ------------------
    public function getStats(Request $request)
    {
        $period = $request->input('period', 'week');
        $dateRange = $this->getDateRange($period);

        // Total Revenue
        $totalRevenue = DB::table('commandes')->whereBetween('created_at', $dateRange)->sum('prix_total');
        $previousRevenue = $this->getPreviousPeriodRevenue($period);
        $revenueChange = $this->calculatePercentageChange($totalRevenue, $previousRevenue);

        // Total Orders
        $totalOrders = DB::table('commandes')->whereBetween('created_at', $dateRange)->count();
        $previousOrders = $this->getPreviousPeriodOrders($period);
        $ordersChange = $this->calculatePercentageChange($totalOrders, $previousOrders);

        // Average Order Value
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $previousAvgOrderValue = $previousOrders > 0 ? $previousRevenue / $previousOrders : 0;
        $avgOrderChange = $this->calculatePercentageChange($avgOrderValue, $previousAvgOrderValue);

        // Customer Satisfaction
        $satisfaction = DB::table('ratings')->whereBetween('created_at', $dateRange)->avg('rating') ?? 0;
        $previousSatisfaction = $this->getPreviousPeriodSatisfaction($period);
        $satisfactionChange = $satisfaction - $previousSatisfaction;

        return response()->json([
            'success' => true,
            'data' => [
                'totalRevenue' => [
                    'value' => round($totalRevenue, 2),
                    'change' => round($revenueChange, 1),
                    'trend' => $revenueChange >= 0 ? 'up' : 'down'
                ],
                'totalOrders' => [
                    'value' => $totalOrders,
                    'change' => round($ordersChange, 1),
                    'trend' => $ordersChange >= 0 ? 'up' : 'down'
                ],
                'avgOrderValue' => [
                    'value' => round($avgOrderValue, 2),
                    'change' => round($avgOrderChange, 1),
                    'trend' => $avgOrderChange >= 0 ? 'up' : 'down'
                ],
                'customerSatisfaction' => [
                    'value' => round($satisfaction, 1),
                    'maxValue' => 5,
                    'change' => round($satisfactionChange, 1),
                    'trend' => $satisfactionChange >= 0 ? 'up' : 'down'
                ]
            ]
        ]);
    }

    // ------------------ Revenue Trends ------------------
    public function getRevenueTrends(Request $request)
    {
        $period = $request->input('period', 'week');
        $current = $this->getRevenueByPeriod($period, 'current');
        $previous = $this->getRevenueByPeriod($period, 'previous');

        return response()->json([
            'success' => true,
            'data' => [
                'current' => $current,
                'previous' => $previous
            ]
        ]);
    }

    // ------------------ Top Categories ------------------
    public function getTopCategories(Request $request)
    {
        $period = $request->input('period', 'week');
        $dateRange = $this->getDateRange($period);

       $topCategories = DB::table('commande_plat')
    ->join('plats', 'commande_plat.plat_id', '=', 'plats.id')
    ->join('categories', 'plats.category_id', '=', 'categories.id')
    ->join('commandes', 'commande_plat.commande_id', '=', 'commandes.id')
    ->select('categories.nom', DB::raw('SUM(commande_plat.quantite) as total_sold'))
    ->whereBetween('commandes.created_at', $dateRange)
    ->groupBy('categories.nom')
    ->orderByDesc('total_sold')
    ->get();


        return response()->json(['success' => true, 'data' => $topCategories]);
    }

    // ------------------ Payment Methods ------------------
    public function getPaymentMethods(Request $request)
    {
        $period = $request->input('period', 'month');
        $dateRange = $this->getDateRange($period);
$total = DB::table('commandes')
    ->whereBetween('created_at', $dateRange)
    ->count();
$payments = DB::table('commandes')
    ->select('paymentMethod', DB::raw('COUNT(*) as total'))
    ->whereBetween('created_at', $dateRange)
    ->groupBy('paymentMethod')
    ->get();
$result = $payments->map(function ($item) use ($total) {
    return [
        'method' => $item->paymentMethod,
        'percentage' => round(($item->total / $total) * 100, 2)
    ];
});

    }

    // ------------------ Top Products ------------------
public function getTopProducts(Request $request)
{
    $limit = $request->input('limit', 5);

    $topProducts = DB::table('commande_plat')
        ->join('plats', 'commande_plat.plat_id', '=', 'plats.id')
        ->select('plats.nom', DB::raw('SUM(commande_plat.quantite) as total_sold'))
        ->groupBy('plats.nom')
        ->orderByDesc('total_sold')
        ->limit($limit)
        ->get();

    return response()->json([
        'success' => true,
        'data' => $topProducts
    ]);
}



    // ------------------ Peak Hours ------------------
    public function getPeakHours()
    {
        $data = DB::table('commandes')
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as orders'))
            ->groupBy('hour')
            ->orderByDesc('orders')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ------------------ Customer Metrics ------------------
   public function getCustomerMetrics(Request $request)
{
    $period = $request->input('period', 'week');
    $dateRange = $this->getDateRange($period);
    $start = $dateRange[0]->format('Y-m-d H:i:s');
    $end = $dateRange[1]->format('Y-m-d H:i:s');

    $data = DB::table('users')
        ->select(DB::raw("COUNT(*) as count"), DB::raw("CASE WHEN created_at BETWEEN '$start' AND '$end' THEN 'new' ELSE 'returning' END as type"))
        ->groupBy('type')
        ->get();

    return response()->json(['success' => true, 'data' => $data]);
}


    // ------------------ Export Report ------------------
    public function exportReport(Request $request)
    {
        $period = $request->input('period', 'week');
        $stats = $this->getStats($request)->getData()->data;

        return response()->json([
            'success' => true,
            'message' => 'Report generated successfully',
            'downloadUrl' => '/downloads/report_' . time() . '.pdf'
        ]);
    }

    // ------------------ Private Helpers ------------------
    private function getDateRange($period)
    {
        switch ($period) {
            case 'today': return [Carbon::today(), Carbon::now()];
            case 'week': return [Carbon::now()->startOfWeek(), Carbon::now()];
            case 'month': return [Carbon::now()->startOfMonth(), Carbon::now()];
            default: return [Carbon::now()->startOfWeek(), Carbon::now()];
        }
    }

    private function getPreviousDateRange($period)
    {
        switch ($period) {
            case 'today': return [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()];
            case 'week': return [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()];
            case 'month': return [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()];
            default: return [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()];
        }
    }

    private function getPreviousPeriodRevenue($period)
    {
        $range = $this->getPreviousDateRange($period);
        return DB::table('commandes')->whereBetween('created_at', $range)->sum('prix_total');
    }

    private function getPreviousPeriodOrders($period)
    {
        $range = $this->getPreviousDateRange($period);
        return DB::table('commandes')->whereBetween('created_at', $range)->count();
    }

    private function getPreviousPeriodSatisfaction($period)
    {
        $range = $this->getPreviousDateRange($period);
        return DB::table('ratings')->whereBetween('created_at', $range)->avg('rating') ?? 0;
    }

    private function getRevenueByPeriod($period, $type = 'current')
    {
        $range = $type === 'current' ? $this->getDateRange($period) : $this->getPreviousDateRange($period);
        $data = DB::table('commandes')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(prix_total) as revenue'))
            ->whereBetween('created_at', $range)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $data->map(fn($item) => ['date' => $item->date, 'value' => round($item->revenue, 2)]);
    }

    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) return 100;
        return (($current - $previous) / $previous) * 100;
    }
}
