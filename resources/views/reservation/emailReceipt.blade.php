<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-sm p-4 mb-5" style="max-width: 700px; margin: auto; border-radius: 10px;">
            <h3 class="text-center mb-3">Hey, {{ $reservation->name }}</h3>
            <p class="text-center">This is the receipt for your reservation.</p>
    
            <div class="row">
                <div class="col-6">
                    <p><strong>Payment No:</strong> #{{ $reservation->id }}</p>
                </div>
                <div class="col-6 text-end">
                    <p><strong>Payment Date:</strong> {{ date('M d, Y', strtotime($reservation->created_at)) }}</p>
                </div>
            </div>
    
            <hr>
    
            <div class="row">
                <div class="col-6">
                    <h6>Customer</h6>
                    <p>
                        <strong>{{ $reservation->name }}</strong><br>
                        {{ $reservation->phone }}<br>
                        <a href="mailto:{{ $reservation->email }}">{{ $reservation->email }}</a>
                    </p>
                </div>
                <div class="col-6 text-end">
                    <h6>Payment To</h6>
                    <p>
                        <strong>YoruHotel</strong><br>
                        <a href="mailto:yoruuuteamm@gmail.com">yoruuuteamm@gmail.com</a>
                    </p>
                </div>
            </div>
    
            <hr>
    
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Room Type - {{ $reservation->room->type }}</td>
                        <td class="text-end">Rp{{ number_format($reservation->room->price, 0, ',', '.') }} / night</td>
                    </tr>
                    <tr>
                        <td>Stay Duration</td>
                        <td class="text-end">
                            @php
                                $totalNights = max(1, (strtotime($reservation->check_out) - strtotime($reservation->check_in)) / 86400);
                            @endphp
                            {{ $totalNights }} nights
                        </td>
                    </tr>
                    <tr>
                        <td>Subtotal</td>
                        <td class="text-end">Rp{{ number_format($reservation->total_price, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th class="text-end text-success">Rp{{ number_format($reservation->total_price, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
    
            <div class="text-center mt-4">
                <a href="{{ url('/dashboard') }}" class="btn btn-dark">Back</a>
            </div>
        </div>
    </div>
    
</body>
</html>




