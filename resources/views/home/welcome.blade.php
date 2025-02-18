@extends('home.dashboard')

@section('home')

<style>
    .large-btn {
        font-size: 20px; /* Ukuran font lebih besar */
        padding: 15px 50px; /* Padding besar */
        border-radius: 1px; /* Membuat sudut tombol lebih membulat */
    }
</style>

<!-- Hero Section -->
<div class="position-relative w-100" style="height: 550px;">
    <img src="{{ asset('assets/images/slide1.jpg') }}" class="img-fluid w-100 h-100 object-fit-cover" alt="Hero Image">
    <a href="{{ url('/reservations') }}" class="btn btn-light large-btn shadow btn-rounded position-absolute top-50 start-50 translate-middle" data-mdb-ripple-init data-mdb-ripple-color="dark">
        Book Now
    </a>
    
    
    
</div>

<!-- Content Section -->
<div class="container mt-5">
    <h3 class="text-center text-light">BATAVIA</h3>
    <p class="text-center text-light">Elegant Dutch-era architecture, flea markets, and ethnic cuisine</p>
    
    <div class="row mt-5 align-items-center">
        <div class="col-md-6">
            <img src="{{ asset('assets/images/jkt2.jpg') }}" class="img-fluid rounded" alt="Jakarta">
        </div>
        <div class="col-md-6">
            <h5 class="text-light">Downtown Jakarta is a monument to the future of Indonesia, intriguing and chaotic by turn. 
                As capital city of a leading economy, its skyline is dappled with the skyscraper homes of banks and corporations.</h5>
            <p class="text-light">
                To delve behind this 21st-century façade, however, is to discover the city’s complex heritage. 
                Feel the pull of Islam in the imposing Istiqlal Mosque, adorned with calligraphy. 
                Seek out the elegant Dutch colonial buildings of Kota Tua, and admire city panoramas 
                from the observation deck of the Monas Tower, proud symbol of Indonesian independence in 1945.
            </p>
            <p class="text-light">
                Wooden schooners, pivotal to the spice trade on which Indonesia grew rich, 
                line up along the quay at Sunda Kelapa Port on Ciliwung River.
            </p>
        </div>
    </div>
</div>

@endsection
