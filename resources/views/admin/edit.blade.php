@extends('admin.dashboard')
@section('admin')
<div class="row">
    <div class="col-md-12 grid-margin stretch-card"> <!-- Fixed col-md-12 -->
        <div class="card">
            <div class="card-body">
                <h1 class="card-title">Update Room</h1>
                <form action="{{ route('rooms.update', $room->id) }}" method="POST"> <!-- Fixed route -->
                    @csrf
                    @method('PUT') <!-- Fixed method -->
                    
                    <div class="mb-3">
                        <label class="form-label">Number Room</label>
                        <input type="text" class="form-control" id="number" name="number" value="{{ $room->number }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type Room</label>
                        <input type="text" class="form-control" id="type" name="type" value="{{ $room->type }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" value="{{ $room->price }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Facilities Room</label>
                        <textarea name="facilities" class="form-control" id="facilities" rows="3" required>{{ $room->facilities }}</textarea> <!-- Fixed variable name -->    
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="available" {{ $room->status == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="booked" {{ $room->status == 'booked' ? 'selected' : '' }}>Booked</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Room Image</label>
                        <br>
                        <img src="{{asset($room->image)}}" alt="Room Image" width="250px">
                        <br><br>
                        <input type="file" name="image" class="form-control">
                        <small class="text-muted">stay empty if not changing</small>
                    </div>
                    
                    <button class="btn btn-primary" type="submit">Update</button>
                    <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
