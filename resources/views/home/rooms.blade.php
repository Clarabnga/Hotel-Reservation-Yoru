    @extends('home.dashboard')

    @section('home')

    <div class="container mt-5">
        <h2 class="text-center mb-5 mt-5 pt-3 fw-bold text-uppercase text-light">Our Rooms</h2>
        <div class="row">
            @foreach ($rooms as $room)
            <div class="col-md-4 mb-4">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden room-card">
                    <img src="{{ asset($room->image) }}" alt="{{ $room->type }}" class="card-img-top">
                    <div class="card-body">
                        <h5 class="card-title fw-semibold text-primary">{{ $room->type }}</h5>
                        <p class="card-text text-muted">
                            <strong class="text-dark">Price:</strong> 
                            <span class="fs-5 fw-bold text-danger">Rp {{ number_format($room->price, 0, ',', '.') }}</span> / night
                            <br>
                            {{ Str::limit($room->facilities, 500) }}
                        </p>
                        <a href="{{url('/booking/' . $room->id)}}" class="btn btn-dark w-100 rounded-pill">Book Now</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <style>
    /* Efek hover untuk kartu kamar */
    .room-card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* Membuat card-body mengisi sisa ruang */
    .room-card .card-body {
        flex-grow: 1; /* Membuat card-body mengisi ruang yang tersisa */
    }

    /* Menjamin panjang kartu seragam */
    .card {
        min-height: 450px; /* Sesuaikan tinggi kartu sesuai kebutuhan */
        display: flex;
        flex-direction: column;
    }

    /* Styling untuk tombol */
    .room-card .btn {
        margin-top: auto; /* Memastikan tombol berada di bagian bawah kartu */
    }

    .room-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    </style>

    @endsection
