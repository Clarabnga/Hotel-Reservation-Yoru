@extends('home.dashboard')

@section('home')
<div class="container py-5 text-light">
    <h2 class="mb-4">My Reservations</h2>

    @foreach ([
        'Upcoming' => $upcomingReservations,
        'Past' => $pastReservations,
        'Cancelled' => $cancelledReservations,
    ] as $label => $reservations)
        <h4 class="mt-4">{{ $label }}</h4>
        <div class="table-responsive">
            <table class="table table-dark table-striped">
                <thead><tr><th>Room</th><th>Dates</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse ($reservations as $reservation)
                    <tr>
                        <td>{{ $reservation->room->type }} #{{ $reservation->room->formatted_number }}</td>
                        <td>{{ $reservation->check_in }} — {{ $reservation->check_out }}</td>
                        <td>{{ ucfirst($reservation->status) }}</td>
                        <td class="d-flex gap-2">
                            <a class="btn btn-sm btn-light" href="{{ route('receipt', $reservation) }}">Receipt</a>
                            @can('cancel', $reservation)
                                <form method="POST" action="{{ route('reservations.cancel', $reservation) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-danger" type="submit">Cancel</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No {{ strtolower($label) }} reservations.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
</div>
@endsection
