<?php

namespace App\Http\Controllers;

use App\Models\PriorityQueue;
use App\Models\WatchdogEvent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WatchdogDashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['waiting', 'processing', 'retry_wait', 'completed', 'failed'])],
            'priority' => ['nullable', Rule::in(['1', '2', '3'])],
        ]);

        $statusCounts = PriorityQueue::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $jobs = PriorityQueue::query()
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('current_priority', $priority))
            ->orderByRaw("CASE status WHEN 'processing' THEN 0 WHEN 'waiting' THEN 1 WHEN 'retry_wait' THEN 2 WHEN 'failed' THEN 3 ELSE 4 END")
            ->orderBy('current_priority')
            ->orderBy('queue_sequence')
            ->paginate(20)
            ->withQueryString();

        $lanes = PriorityQueue::query()
            ->where('status', 'waiting')
            ->orderBy('current_priority')
            ->orderBy('queue_sequence')
            ->get()
            ->groupBy('current_priority');

        $recentEvents = WatchdogEvent::with('job:id,uuid,current_priority')
            ->latest('id')
            ->limit(25)
            ->get();

        return view('admin.watchdog.index', compact('statusCounts', 'jobs', 'lanes', 'recentEvents', 'filters'));
    }
}
