@extends('admin.dashboard')

@section('admin')
@php
    $statuses = ['waiting', 'processing', 'retry_wait', 'completed', 'failed'];
    $laneNames = [1 => 'VVIP', 2 => 'VIP', 3 => 'Regular'];
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h3 class="mb-1">Watchdog V2</h3><small class="text-muted">Live database state · refreshed {{ now()->format('M j, Y H:i:s') }}</small></div>
        <a href="{{ route('admin.watchdog.index', $filters) }}" class="btn btn-outline-secondary">Refresh</a>
    </div>

    <div class="row g-3 mb-4">
        @foreach ($statuses as $status)
        <div class="col-6 col-lg"><div class="card h-100"><div class="card-body">
            <div class="text-muted text-uppercase small">{{ str_replace('_', ' ', $status) }}</div>
            <div class="fs-2 fw-bold">{{ $statusCounts[$status] ?? 0 }}</div>
        </div></div></div>
        @endforeach
    </div>

    <h5>Active priority lanes</h5>
    <div class="row g-3 mb-4">
        @foreach ($laneNames as $priority => $name)
        <div class="col-lg-4"><div class="card h-100"><div class="card-header fw-bold">{{ $name }} · Priority {{ $priority }}</div><div class="card-body">
            @forelse (($lanes[$priority] ?? collect())->take(8) as $job)
                <div class="border rounded p-2 mb-2">
                    <code>{{ Str::limit($job->uuid, 13) }}</code>
                    <span class="float-end">#{{ $job->queue_sequence }}</span><br>
                    <small>Base {{ $job->base_priority }} · Beats {{ $job->beat_count }} · Attempts {{ $job->attempt_count }}/{{ $job->max_attempts }}</small>
                </div>
            @empty <span class="text-muted">Lane empty</span> @endforelse
        </div></div></div>
        @endforeach
    </div>

    <form class="row g-2 mb-3" method="GET">
        <div class="col-md-4"><select name="status" class="form-select"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach</select></div>
        <div class="col-md-4"><select name="priority" class="form-select"><option value="">All priorities</option>@foreach($laneNames as $priority => $name)<option value="{{ $priority }}" @selected(($filters['priority'] ?? '') == $priority)>{{ $name }}</option>@endforeach</select></div>
        <div class="col-md-4"><button class="btn btn-secondary" type="submit">Apply filters</button></div>
    </form>

    <div class="table-responsive mb-4"><table class="table table-striped align-middle"><thead><tr><th>UUID / Class</th><th>Status</th><th>Priority</th><th>Beats</th><th>Attempts</th><th>Sequence</th><th>Available</th><th>Last error</th></tr></thead><tbody>
        @forelse($jobs as $job)<tr><td><code>{{ $job->uuid }}</code><br><small>{{ class_basename($job->job_class) }}</small></td><td>{{ $job->status }}</td><td>{{ $job->base_priority }} → {{ $job->current_priority }}</td><td>{{ $job->beat_count }}</td><td>{{ $job->attempt_count }}/{{ $job->max_attempts }}</td><td>{{ $job->queue_sequence }}</td><td>{{ $job->available_at?->format('M j H:i:s') ?? 'Now' }}</td><td title="{{ $job->last_error }}">{{ Str::limit($job->last_error, 40) }}</td></tr>
        @empty<tr><td colspan="8" class="text-center text-muted">No jobs match these filters.</td></tr>@endforelse
    </tbody></table></div>
    {{ $jobs->links() }}

    <h5 class="mt-4">Recent events</h5>
    <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Time</th><th>Event</th><th>Job</th><th>Details</th></tr></thead><tbody>
        @forelse($recentEvents as $event)<tr><td>{{ $event->created_at->format('M j H:i:s') }}</td><td>{{ $event->event_type }}</td><td><code>{{ Str::limit($event->job?->uuid, 13) }}</code></td><td><small>{{ $event->metadata ? json_encode($event->metadata) : '—' }}</small></td></tr>@empty<tr><td colspan="4" class="text-muted">No events recorded.</td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection
