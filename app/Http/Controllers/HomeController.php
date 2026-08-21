<?php

namespace App\Http\Controllers;

use App\Models\Home;
use App\Models\RoomType;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredRoomTypes = RoomType::where('active', true)->withMin(['rooms' => fn ($q) => $q->active()], 'price')->limit(3)->get();

        return view('home.welcome', compact('featuredRoomTypes'));
    }

    /* Display a listing of the resource.
    */
    public function HomeDashboard()
    {
        return $this->index();
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Home $home)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Home $home)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Home $home)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Home $home)
    {
        //
    }
}
