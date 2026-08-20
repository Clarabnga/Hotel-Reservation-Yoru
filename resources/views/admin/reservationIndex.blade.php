@extends('admin.dashboard')

@section('admin')

<div class="container-fluid mt-5 mb-5"> 
    <form method="GET" action="{{ route('admin.reservation') }}" class="row g-2 mb-3">
        <div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search customer, email, or room type"></div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach (['pending', 'confirmed', 'cancelled', 'completed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-secondary" type="submit">Filter</button></div>
    </form>
    
    <div class="table-responsive"> 
        <table class="table mb-5">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Room Type</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Detail Reservation</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reservations as $reservation)
                <tr>
                    <td>{{ $reservation->id }}</td>
                    <td>{{ $reservation->name }}</td>
                    <td>{{ $reservation->room->type }}</td>
                    <td>{{ $reservation->check_in }}</td>
                    <td>{{ $reservation->check_out }}</td>
                    <td>
                        <span class="badge 
                        @if ($reservation->status == 'pending') bg-warning
                        @elseif($reservation->status == 'confirmed') bg-success
                        @elseif($reservation->status == 'cancelled') bg-danger
                        @elseif($reservation->status == 'completed') bg-info
                        @endif">
                        {{ ucfirst($reservation->status) }} 
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('update.reservation', $reservation->id) }}" method="post">
                            @csrf
                            <select name="status" class="form-select d-inline-block w-auto">
                                <option value="pending" {{ $reservation->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $reservation->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ $reservation->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="completed" {{ $reservation->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            <button type="submit" class="btn btn-secondary btn-sm">Update</button>
                        </form>
                    </td>
                    <td>
                        <a href="{{url('/receipt/'. $reservation->id)}}" class="btn btn-primary"> Receipt </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $reservations->links() }}

</div>

@endsection
