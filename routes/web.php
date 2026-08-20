<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\WatchdogDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home.welcome')->name('home');
Route::view('/home/facilities', 'home.facilities')->name('home.facilities');
Route::get('/our-rooms', [RoomController::class, 'OurRooms'])->name('our.room');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        if ($request->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return app(HomeController::class)->HomeDashboard();
    })->name('dashboard');

    Route::redirect('/home/dashboard', '/dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/receipt/{reservation}', [ReservationController::class, 'showReceipt'])->name('receipt');
    Route::get('/booking/{id}', [ReservationController::class, 'bookingForm'])->name('booking.form');
    Route::post('/booking', [ReservationController::class, 'store'])->name('booking.store');
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');
    Route::get('/profile', [AdminController::class, 'AdminProfile'])->name('admin.profile');
    Route::post('/profile', [AdminController::class, 'AdminProfileUpdate'])->name('admin.profile.update');
    Route::resource('/rooms', RoomController::class)->names('rooms');
    Route::get('/reservations', [AdminController::class, 'AdminReservation'])->name('admin.reservation');
    Route::post('/reservations/{reservation}/status', [AdminController::class, 'UpdateReservation'])->name('update.reservation');
    Route::get('/watchdog', [WatchdogDashboardController::class, 'index'])->name('admin.watchdog.index');
});

require __DIR__.'/auth.php';
