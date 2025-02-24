@extends('home.dashboard')
@section('home')

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body bg-secondary">

                <form class="forms-sample" action="{{url('/booking')}}" method="post">
                    @csrf

                    <input type="hidden" name="room_id" value="{{$room->id}}">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" placeholder="Full Name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" placeholder="Phone" name="phone"required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="check_in" class="form-label">Check-In</label>
                            <input type="date" class="form-control" name="check_in" id="checkIn" required>
                        </div>
                    
                        <div class="col-md-6">
                            <label for="check_out" class="form-label">Check-Out</label>
                            <input type="date" class="form-control" name="check_out" id="checkOut" required>
                        </div>
                    </div>
                  
                    <button type="submit"  class="mt-3 py-2 w-100 btn btn-dark ">Confirm Booking</button>
                
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        var dtToday = new Date();
        var month = dtToday.getMonth() + 1;
        var day = dtToday.getDate();
        var year = dtToday.getFullYear();

        if (month < 10) month = '0' + month;
        if (day < 10) day = '0' + day;

        var minDate = year + '-' + month + '-' + day;
        $('#checkIn').attr('min', minDate);

        // Atur min untuk Check-Out agar lebih dari Check-In
        $('#checkIn').on('change', function() {
            var checkInDate = $(this).val();
            $('#checkOut').attr('min', checkInDate);
        });
    });
</script>

@endsection
`