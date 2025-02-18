@extends('admin.dashboard')
@section('admin')

<style>
  .facilities-column {
      white-space: normal !important; /* Biar teks bisa turun ke bawah */
      word-wrap: break-word; /* Agar teks panjang bisa dipisah */
      max-width: 300px; /* Batas lebar kolom */
  }
</style>


<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">

        <h6 class="card-title">Data Kamar</h6>
        <a href="{{route('rooms.create')}}" class="btn btn-secondary">Add Room</a>
        <div class="table-responsive">
          <table id="dataTableExample" class="table">
            <thead>
              <tr>
                <th>Number Room</th>
                <th>Type Room</th>
                <th>Price</th>
                <th>Facilities</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($rooms as $room)
              <tr>
                <td>{{ $room->formatted_number }}</td>
                <td>{{ $room->type}}</td>
                <td>{{ $room->formatted_price}}</td>
                <td class="facilities-column">{{ $room->facilities}}</td>
                <td>{{ $room->status}} </td>
                <td>
                  <div class="d-flex gap-2">
                    <a href="{{route('rooms.edit', $room->id)}}" class="btn btn-light">Edit</a>
                    <form action="{{route('rooms.destroy', $room->id)}}" method="post">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-info">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      
      </div>
    </div>
  </div>
</div>

@endsection
