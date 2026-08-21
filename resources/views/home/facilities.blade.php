@extends('home.dashboard') @section('title','Facilities — Yoru Hotel') @section('home')
<header class="page-hero page-hero-split"><div class="shell"><div><p class="eyebrow">Restore your rhythm</p><h1>Space for every<br>part of your day.</h1><p>From a slow morning swim to a focused afternoon meeting, every shared space is designed around comfort and ease.</p></div><img src="{{ asset('assets/images/pool.jpeg') }}" alt="Yoru Hotel pool and wellness area"></div></header>
<section class="section shell editorial-list">
@foreach([
['restaurant.jpg','Dining','Restaurant','Japanese-influenced dishes, familiar international favourites and seasonal ingredients, served from breakfast through dinner.','Breakfast 06:30–10:30 · Dinner until 22:00'],
['lounge.jpg','Pause','Yoru Lounge','A calm setting for tea, informal meetings or an unhurried hour with a book. Quiet corners and attentive service make it easy to settle in.','Daily 08:00–23:00'],
['massage.jpg','Restore','Wellness & massage','Private treatments help release the weight of travel. Our team can recommend a session based on the time and pace of your stay.','By appointment · Guest priority'],
['ballroom.jpg','Gather','Meetings & events','Adaptable rooms for board meetings, workshops and intimate celebrations, supported by presentation equipment, catering and a dedicated coordinator.','Up to 80 guests · Custom layouts']
] as [$image,$eyebrow,$title,$copy,$meta])
<article class="editorial-row"><img loading="lazy" src="{{ asset('assets/images/'.$image) }}" alt="{{ $title }} at Yoru Hotel"><div><p class="eyebrow">{{ $eyebrow }}</p><h2>{{ $title }}</h2><p>{{ $copy }}</p><p class="detail-note">{{ $meta }}</p></div></article>
@endforeach
</section>
<section class="soft-section section"><div class="shell feature-callout"><div><p class="eyebrow">Everything close at hand</p><h2>Designed for an effortless stay.</h2></div><p>Hotel guests also enjoy complimentary Wi-Fi, a modern fitness studio, luggage assistance and a 24-hour front desk. Ask our team when you arrive and we will help shape the day around you.</p><a class="button" href="{{ route('our.room') }}">Plan your stay</a></div></section>@endsection
