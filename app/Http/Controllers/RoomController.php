<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();

        return view('admin.index', compact('rooms'));
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
            'price' => 'required|integer|min:0',
            'facilities' => 'required|string|max:5000',
            'operational_status' => 'required|in:active,inactive,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $imagePath = $request->file('image')?->store('rooms', 'public');

        Room::create([
            'number' => $request->number,
            'type' => $request->type,
            'price' => $request->price,
            'facilities' => $request->facilities,
            'status' => 'available',
            'operational_status' => $request->operational_status,
            'image' => $imagePath ? 'storage/'.$imagePath : null,
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
            'number' => ['required', 'string', 'max:3', Rule::unique('rooms', 'number')->ignore($room->id)],
            'type' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'facilities' => ['required', 'string', 'max:5000'],
            'operational_status' => ['required', Rule::in(['active', 'inactive', 'maintenance'])],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $room->update($request->only(['number', 'type', 'price', 'facilities', 'operational_status']));

        if ($request->hasFile('image')) {
            $this->deleteManagedImage($room->image);
            $imagePath = $request->file('image')->store('rooms', 'public');
            $room->image = 'storage/'.$imagePath;
            $room->save();
        }

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {

        $this->deleteManagedImage($room->image);
        $room->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Kamar Berhasil dihapus');
        //
    }

    public function OurRooms()
    {
        $rooms = Room::active()->select('id', 'facilities', 'type', 'image', 'price')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MIN(id)'))
                    ->from('rooms')
                    ->groupBy('type');
            })
            ->get();

        return view('home.rooms', compact('rooms'));
    }

    private function deleteManagedImage(?string $image): void
    {
        if ($image && str_starts_with($image, 'storage/rooms/')) {
            Storage::disk('public')->delete(substr($image, strlen('storage/')));
        }
    }

    //
}
