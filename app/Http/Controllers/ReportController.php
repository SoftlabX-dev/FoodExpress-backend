<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $report = Report::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully',
            'report' => $report,
        ], 201);
    }

    // Récupérer les KPIs
    public function getKPIs()
    {
        $kpis = [
            'total_reports' => Report::count(),
            'unread' => Report::unread()->count(),
            'read' => Report::read()->count(),
            'resolved' => Report::resolved()->count(),
            
            'high_priority' => Report::where('priority', 'high')->count(),
            'medium_priority' => Report::where('priority', 'medium')->count(),
            'low_priority' => Report::where('priority', 'low')->count(),
            
            'avg_resolution_time' => Report::whereNotNull('resolved_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_time'))
                ->first()
                ->avg_time ?? 0,
            
            'resolution_rate' => Report::count() > 0 
                ? round((Report::resolved()->count() / Report::count()) * 100, 2) 
                : 0,
            
            'overdue_reports' => Report::where('status', '!=', 'resolved')
                ->where('created_at', '<', now()->subDay())
                ->count(),
        ];

        return response()->json($kpis);
}

  // Liste tous les reports avec pagination
    public function index(Request $request)
    {
        $query = Report::query();

        // Filtres
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $reports = $query->orderBy('created_at', 'desc')
                        ->paginate(10);

        return response()->json($reports);
    }
    // Marquer comme lu
    public function markAsRead($id)
    {
        $report = Report::findOrFail($id);
        
        if ($report->status === 'unread') {
            $report->update([
                'status' => 'read',
                'read_at' => now()
            ]);
        }

        return response()->json($report);
    }

    // Marquer comme résolu
    public function markAsResolved($id)
    {
        $report = Report::findOrFail($id);
        
        $report->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'read_at' => $report->read_at ?? now()
        ]);

        return response()->json($report);
    }

    // Supprimer un report
    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json(['message' => 'Report deleted successfully']);
    }

    // Dashboard avec statistiques détaillées
    public function dashboard()
    {
        return response()->json([
            'kpis' => $this->getKPIs()->getData(),
            'recent_reports' => Report::orderBy('created_at', 'desc')->limit(5)->get(),
            'priority_distribution' => [
                'high' => Report::where('priority', 'high')->count(),
                'medium' => Report::where('priority', 'medium')->count(),
                'low' => Report::where('priority', 'low')->count(),
            ],
            'status_distribution' => [
                'unread' => Report::unread()->count(),
                'read' => Report::read()->count(),
                'resolved' => Report::resolved()->count(),
            ],
        ]);
    }
}