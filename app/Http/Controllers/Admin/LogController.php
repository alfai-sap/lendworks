<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'all');
        $period = $request->input('period', '7');
        $level = $request->input('level', 'all');
        $search = $request->input('search');

        $query = \DB::table('activity_log')
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->where('log_type', $type);
            })
            ->when($period !== 'all', function ($query) use ($period) {
                return $query->where('created_at', '>=', now()->subDays($period));
            })
            ->when($level !== 'all', function ($query) use ($level) {
                return $query->where('level', $level);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('properties', 'like', "%{$search}%");
                });
            });

        // Get stats for each log type
        $stats = [
            'system' => \DB::table('activity_log')->where('log_type', 'system')->count(),
            'user' => \DB::table('activity_log')->where('log_type', 'user')->count(),
            'admin' => \DB::table('activity_log')->where('log_type', 'admin')->count(),
            'error' => \DB::table('activity_log')->where('level', 'error')->count(),
        ];

        // Get paginated logs with proper ordering
        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->through(function ($log) {
                return [
                    'id' => $log->id,
                    'type' => $log->log_type,
                    'level' => $log->level,
                    'description' => $log->description,
                    'properties' => json_decode($log->properties, true),
                    'created_at' => $log->created_at,
                    'causer_type' => $log->causer_type,
                    'causer_id' => $log->causer_id,
                ];
            });

        return Inertia::render('Admin/Logs', [
            'logs' => $logs,
            'stats' => $stats,
            'filters' => $request->only(['type', 'period', 'level', 'search'])
        ]);
    }

    private function getLogs($type, $period, $level, $search)
    {
        $query = \DB::table('activity_log')
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->where('log_type', $type);
            })
            ->when($period !== 'all', function ($query) use ($period) {
                return $query->where('created_at', '>=', now()->subDays($period));
            })
            ->when($level !== 'all', function ($query) use ($level) {
                return $query->where('level', $level);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('properties', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return $query;
    }

    private function getLogCount($type)
    {
        return \DB::table('activity_log')
            ->where('log_type', $type)
            ->count();
    }

    public function export(Request $request)
    {
        try {
            $logs = $this->getLogs(
                $request->input('type', 'all'),
                $request->input('period', 'all'),
                $request->input('level', 'all'),
                $request->input('search')
            );

            $output = '';
            $headers = ['Date', 'Type', 'Level', 'Description', 'User', 'Details'];
            $output .= implode(',', $headers) . "\n";

            foreach ($logs as $log) {
                $row = [
                    $log->created_at,
                    $log->log_type,
                    $log->level,
                    '"' . str_replace('"', '""', $log->description) . '"',
                    $log->causer_type ? ($log->causer_type . ':' . $log->causer_id) : 'System',
                    '"' . str_replace('"', '""', json_encode($log->properties)) . '"'
                ];
                $output .= implode(',', $row) . "\n";
            }

            return response($output, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=logs_export_' . date('Y-m-d') . '.csv',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ]);
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
}
