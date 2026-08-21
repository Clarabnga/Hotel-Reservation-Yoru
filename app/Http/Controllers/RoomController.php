<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
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
        return view('admin.create', ['roomTypes' => RoomType::where('active', true)->orderBy('name')->get()]);
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|string|unique:rooms|max:3',
            'room_type_id' => ['nullable', 'exists:room_types,id'],
            'type' => 'required|string',
            'price' => 'required|integer|min:0',
            'facilities' => 'required|string|max:5000',
            'operational_status' => 'required|in:active,inactive,maintenance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $imagePath = $request->file('image')?->store('rooms', 'public');

        $roomType = $request->filled('room_type_id') ? RoomType::findOrFail($request->integer('room_type_id')) : null;
        Room::create([
            'room_type_id' => $roomType?->id,
            'number' => $request->number,
            'type' => $roomType?->name ?? $request->type,
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

        return $this->edit($room);
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        $roomTypes = RoomType::where('active', true)->orderBy('name')->get();

        return view('admin.edit', compact('room', 'roomTypes'));
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $request->validate([
            'number' => ['required', 'string', 'max:3', Rule::unique('rooms', 'number')->ignore($room->id)],
            'room_type_id' => ['nullable', 'exists:room_types,id'],
            'type' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'facilities' => ['required', 'string', 'max:5000'],
            'operational_status' => ['required', Rule::in(['active', 'inactive', 'maintenance'])],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $roomType = $request->filled('room_type_id') ? RoomType::findOrFail($request->integer('room_type_id')) : null;
        $room->update($request->only(['number', 'type', 'price', 'facilities', 'operational_status']) + ['room_type_id' => $roomType?->id, 'type' => $roomType?->name ?? $request->type]);

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

    public function OurRooms(Request $request)
    {
        $validated = $request->validate([
            'check_in' => ['nullable', 'date', 'after_or_equal:today'],
            'check_out' => ['nullable', 'date', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1', 'max:8'],
            'room_type' => ['nullable', 'string', 'max:100'],
        ]);

        $rooms = RoomType::where('active', true)
            ->withMin(['rooms' => fn ($q) => $q->active()], 'price')
            ->when($validated['room_type'] ?? null, fn ($query, $type) => $query->where('slug', $type))
            ->when(($validated['check_in'] ?? null) && ($validated['check_out'] ?? null), fn ($query) => $query->whereHas('rooms', fn ($rooms) => $rooms->active()->whereDoesntHave('reservations', fn ($reservations) => $reservations->where('status', '!=', 'cancelled')->where('check_in', '<', $validated['check_out'])->where('check_out', '>', $validated['check_in']))))
            ->orderBy('name')->paginate(9)->withQueryString();

        $roomTypes = RoomType::where('active', true)->orderBy('name')->get(['name', 'slug']);

        return view('home.rooms', compact('rooms', 'roomTypes'));
    }

    public function publicShow(RoomType $roomType)
    {
        abort_unless($roomType->active, 404);
        $roomType->loadMin(['rooms' => fn ($q) => $q->active()], 'price');
        $bookableRoom = $roomType->rooms()->active()->orderBy('id')->first();

        return view('home.room-detail', compact('roomType', 'bookableRoom'));
    }

    private function deleteManagedImage(?string $image): void
    {
        if ($image && str_starts_with($image, 'storage/rooms/')) {
            Storage::disk('public')->delete(substr($image, strlen('storage/')));
        }
    }

    //
}
