<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index(){
        $rooms = Room::all();
        return view ('admin.index', compact('rooms'));
    }
    public function create()
    {
        return view('admin.create');
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|string|unique:rooms|max:3',
            'type' => 'required|string',
            'price' => 'required|integer',
            'facilities' => 'required|string',
            'status' => 'required|in:available,booked',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Fix max size
        ]);
    
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->move(public_path('assets/image)'), $request->file('image')->getClientOriginalName());
            $imagePath = 'assets/image/' . $request->file('image')->getClientOriginalName();
        }
    
        Room::create([
            'number' => $request->number,
            'type' => $request->type,
            'price' => $request->price,
            'facilities' => $request->facilities,
            'status' => $request->status,
            'image' => $imagePath // Simpan path gambar ke database
        ]);
    
        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil ditambahkan');
    }
    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $room = Room::findOrFail($id);
        return view('admin.edit', compact('room'));
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
    return view('admin.edit', compact('room'));
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
{
    $request->validate([
        'number' => 'sometimes|string|unique:rooms,number,'.$room->id.'|max:3',
        'type' => 'sometimes|string',
        'price' => 'sometimes|integer',
        'facilities' => 'sometimes|string',
        'status' => 'sometimes|in:available,booked',
        'image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048'
    ]);

    $room->update($request->only(['number', 'type', 'price', 'facilities', 'status']));

    if($request->hasFile('image')){
        if($room->image && file_exists(public_path($room->image))){
            unlink(public_path($room->image));
        }
        $imagePath = $request->file('image')->store('assets/image', 'public');
        $room->image = 'storage/' . $imagePath;
        $room->save();
    }

    return redirect()->route('rooms.index')->with('success', 'Kamar berhasil diperbarui');
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {


        $room->delete();

        return redirect()->route('admin.dashboard')->with('success','Kamar Berhasil dihapus');
        //
    }

    public function OurRooms()
    {
        $rooms = Room::select('id', 'facilities', 'type', 'image', 'price')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MIN(id)'))
                    ->from('rooms')
                    ->groupBy('type');
            })
            ->get();
            
    
        return view('home.rooms', compact('rooms'));
    }
    
    
    
    //
}
