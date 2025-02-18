@extends('home.dashboard')

@section('home')

<!-- Title Section -->
<div class="container mt-5 text-center">
    <h1 class="display-3 fade-in pt-3 text-light">Our Facilities</h1>
    <p class="text-light fade-in">
        YoruHotel provides world-class facilities designed to offer luxury, comfort, and an unforgettable experience. 
        From a stunning rooftop infinity pool to a world-class spa, every detail is crafted to meet your needs and enhance your stay.
    </p>
    <hr class="w-50 mx-auto">
</div>

<!-- Facilities List -->
<div class="container mt-5">
    <!-- Swimming Pool -->
    <div class="row mt-4 align-items-center fade-in">
        <div class="col-md-6">
            <img src="{{ asset('assets/images/pool.jpeg') }}" class="img-fluid rounded shadow-lg">
        </div>
        <div class="col-md-6">
            <h2 class="fw-bold text-light"> SWIMMING POOL</h2>
            <p class="text-light">
                Relax and unwind at our **rooftop infinity pool**, offering a breathtaking **panoramic view** of both the mountains 
                and the shimmering city skyline at night. Designed for ultimate relaxation, the pool is equipped with **warm water, 
                exclusive lounge chairs**, and a **poolside bar** serving refreshing cocktails. Whether you're taking a dip 
                under the **golden sunset** or enjoying a night swim under the **starlit sky**, this is the perfect place to escape 
                the hustle and bustle of daily life.
            </p>
        </div>
    </div>

    <!-- Restaurant -->
    <div class="row mt-5 align-items-center fade-in">
        <div class="col-md-6 order-md-2">
            <img src="{{ asset('assets/images/restaurant.jpg') }}" class="img-fluid rounded shadow-lg">
        </div>
        <div class="col-md-6 order-md-1">
            <h2 class="fw-bold text-light">RESTAURANT</h2>
            <p class="text-light">
                Indulge in a **world-class dining experience** at our **24-hour Turmeric Restaurant**, featuring an 
                exquisite selection of **international and traditional cuisines** prepared by our award-winning chefs. 
                Whether you're craving a **hearty breakfast, a gourmet dinner, or a late-night snack**, our diverse menu 
                is tailored to satisfy every taste. Enjoy your meals in an **elegant ambiance** with a stunning view, 
                making every bite a memorable moment.
            </p>
        </div>
    </div>

    <!-- Ballroom -->
    <div class="row mt-5 align-items-center fade-in">
        <div class="col-md-6">
            <img src="{{ asset('assets/images/ballroom.jpg') }}" class="img-fluid rounded shadow-lg">
        </div>
        <div class="col-md-6">
            <h2 class="fw-bold text-light"> BALLROOM</h2>
            <p class="text-light">
                Our **grand ballroom** is the perfect venue for your **special events, business meetings, and elegant receptions**. 
                With a **capacity of up to 200 guests**, the space is equipped with **state-of-the-art lighting, a premium sound system, 
                and customizable seating arrangements** to suit any occasion. Whether it’s a **lavish wedding, a corporate conference, or 
                an exclusive gala**, our ballroom offers a luxurious setting for unforgettable experiences.
            </p>
        </div>
    </div>

    <!-- Lounge -->
    <div class="row mt-5 align-items-center fade-in">
        <div class="col-md-6 order-md-2">
            <img src="{{ asset('assets/images/lounge.jpg') }}" class="img-fluid rounded shadow-lg">
        </div>
        <div class="col-md-6 order-md-1">
            <h2 class="fw-bold text-light">LOUNGE</h2>
            <p class="text-light">
                The **Yoru Lounge** is a **peaceful oasis** designed for both **business and leisure**. Whether you're looking for a 
                quiet place to **enjoy a cup of coffee, hold an informal business meeting, or simply relax with a book**, our lounge 
                offers a **cozy atmosphere with elegant seating and soothing background music**. Experience a perfect blend of 
                **sophistication and comfort** in a setting that feels like home.
            </p>
        </div>
    </div>

    <!-- Spa & Massage -->
    <div class="row mt-5 align-items-center fade-in">
        <div class="col-md-6">
            <img src="{{ asset('assets/images/massage.jpg') }}" class="img-fluid rounded shadow-lg">
        </div>
        <div class="col-md-6">
            <h2 class="fw-bold text-light">YORU MASSAGE & SPA</h2>
            <p class="text-light">
                Step into a **world of relaxation** at our **luxurious spa & massage center**. Our highly trained therapists 
                offer a range of **therapeutic treatments, deep tissue massages, aromatherapy, and hot stone therapy** to 
                rejuvenate your body and mind. Using only the **finest essential oils and spa products**, our treatments are 
                designed to relieve stress, improve circulation, and promote deep relaxation. Whether you need to **unwind 
                after a long day or pamper yourself**, our spa is the perfect escape to **renew your energy**.
            </p>
        </div>
    </div>
</div>

<!-- Custom CSS Animations -->
<style>
    .fade-in {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 1s ease-out forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

@endsection
