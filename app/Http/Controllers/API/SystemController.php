<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Alumni;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    /**
     * Get activity logs with pagination and filtering.
     */
    public function activityLogs(Request $request)
    {
        $query = ActivityLog::query();
        
        // Apply filters
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }
        
        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Get paginated results with related user data
        $logs = $query->with('user')
                     ->orderBy('created_at', 'desc')
                     ->paginate($request->per_page ?? 25);
        
        return response()->json($logs);
    }

    /**
     * Get system statistics.
     */
    public function statistics()
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'admins' => User::role('admin')->count(),
                'editors' => User::role('editor')->count(),
                'viewers' => User::role('viewer')->count(),
            ],
            'alumni' => [
                'total' => Alumni::count(),
                'by_faculty' => Alumni::select('faculty', DB::raw('count(*) as count'))
                                    ->groupBy('faculty')
                                    ->get(),
                'by_degree' => Alumni::select('degree', DB::raw('count(*) as count'))
                                   ->groupBy('degree')
                                   ->get(),
                'by_country' => Alumni::select('country', DB::raw('count(*) as count'))
                                     ->groupBy('country')
                                     ->get(),
                'by_year' => Alumni::select(DB::raw('EXTRACT(YEAR FROM graduation_date) as year'), 
                                          DB::raw('count(*) as count'))
                                 ->groupBy('year')
                                 ->orderBy('year')
                                 ->get(),
            ],
            'activity' => [
                'total_logs' => ActivityLog::count(),
                'recent_actions' => ActivityLog::with('user')
                                            ->orderBy('created_at', 'desc')
                                            ->limit(10)
                                            ->get(),
                'actions_by_type' => ActivityLog::select('action', DB::raw('count(*) as count'))
                                              ->groupBy('action')
                                              ->get(),
            ]
        ];
        
        return response()->json($stats);
    }
}