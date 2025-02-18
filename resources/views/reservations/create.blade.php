@extends('home.dashboard')

@section('home')
    <div class="container">
        <h2>Reservasi Kamar: {{ $room->room_number }} - {{ $room->type }}</h2>

        <form action="{{ route('reservations.store', $room->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="phone">Nomor Telepon</label>
                <input type="text" name="phone" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="guests">Jumlah Tamu</label>
                <input type="number" name="guests" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="check_in">Tanggal Check-In</label>
                <input type="date" name="check_in" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="check_out">Tanggal Check-Out</label>
                <input type="date" name="check_out" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Pesan Kamar</button>
        </form>
    </div>
@endsection
