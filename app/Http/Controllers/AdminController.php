<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Str;
use App\Models\Room;
use App\Models\Reservation;

class AdminController extends Controller
{
    public function AdminDashboard(Request $request){
        $rooms = Room::all();
        return view('admin.index', compact('rooms'));
    }

    public function AdminLogout(Request $request){
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerate();

        return redirect('/login');
    }

    public function AdminProfile(Request $request){
        $data['getRecord'] = User::find(Auth::user()->id);
        return view('admin.profile', $data);
    }

    public function AdminProfileUpdate(Request $request){

        $user = request()->validate([
            'email' => 'required|unique:users,email,'.Auth::user()->id
        ]);

        $user = User::find(Auth::user()->id);
        $user->name = trim($request->name);
        $user->username = trim($request->username);
        $user->email = trim($request->email);
        // $user->password = trim($request->password);
        if(!empty($request->password)){
            $user->password = Hash::make($request->password);
        };
        $user->phone = trim($request->phone);
        $user->save(); 

        return redirect('admin/profile')->with('success','update success');
    }

    public function AdminReservation(){
        $reservations = Reservation::all();
        return view('admin.reservationIndex', compact('reservations'));

    }

    public function UpdateReservation(Request $request, $id){
        $reservation = Reservation::findOrFail($id);
        $oldStatus = $reservation->status;
        $reservation->update([
            'status' => $request->status
        ]);
        if ($reservation->status == 'confirmed'){
            $reservation->room->update(['status' => 'booked']);
        }

        if($oldStatus == 'confirmed' && $reservation->status == 'cancelled'){
            $reservation->room->update(['status' => 'available']);
        }
        

        return redirect()->route('admin.reservation')->with('success', 'Reservation update success');
    }

    
    /**
     * Show the form for creating a new resource.
     */
    
}
