<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use Hash;
use Str;
use App\Models\Room;

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

    
    /**
     * Show the form for creating a new resource.
     */
    
}
