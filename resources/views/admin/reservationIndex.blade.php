@extends('admin.dashboard')

@section('admin')

<div class="container-fluid mt-5 mb-5"> 
    
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

</div>

@endsection
