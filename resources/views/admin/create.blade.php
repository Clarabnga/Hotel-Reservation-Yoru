@extends('admin.dashboard')
@section('admin')
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title">Add New Room</h1>
                <form action="{{route('rooms.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="number" class="form-label">Number Room</label>
                        <input type="text" class="form-control" id="number" name="number" required>
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Type Room</label>
                        <input type="text" class="form-control" id="type" name="type" required>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" required>
                    </div>

                    <div class="mb-3">
                        <label for="facilites" class="form-label">Facilities Room</label>
                        <textarea name="facilities" class="form-control" id="facilites" rows="3" required></textarea>     
                    </div>

                    <div class="m-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="booked">Booked</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Room Picture</label>
                        <input type="file"  name="image" class="form-control">
                    </div>
                    <button class="btn btn-primary" type="submit">Save</button>
                    <a href="{{route('rooms.index')}}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


