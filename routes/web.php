<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BusinessInquiryController;
use App\Http\Controllers\GuestServiceRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\WatchdogDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('health');
Route::redirect('/home/facilities', '/facilities');
Route::view('/facilities', 'home.facilities')->name('facilities');
Route::view('/offers', 'home.offers')->name('offers');
Route::view('/about', 'home.about')->name('about');
Route::view('/access', 'home.access')->name('access');
Route::get('/business', [BusinessInquiryController::class, 'create'])->name('business');
Route::post('/business/inquiries', [BusinessInquiryController::class, 'store'])->name('business.inquiries.store');
Route::get('/our-rooms', [RoomController::class, 'OurRooms'])->name('our.room');
Route::get('/our-rooms/{roomType}', [RoomController::class, 'publicShow'])->name('rooms.show.public');

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
    Route::patch('/preferences', [ProfileController::class, 'updatePreferences'])->name('preferences.update');
    Route::get('/requests', [GuestServiceRequestController::class, 'index'])->name('guest-requests.index');
    Route::post('/requests', [GuestServiceRequestController::class, 'store'])->name('guest-requests.store');

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
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('admin.users.show');
    Route::patch('/users/{user}/membership', [AdminController::class, 'updateMembership'])->name('admin.users.membership');
    Route::get('/business', [AdminController::class, 'businessInquiries'])->name('admin.business.index');
    Route::get('/business/{businessInquiry}', [AdminController::class, 'showBusinessInquiry'])->name('admin.business.show');
    Route::patch('/business/{businessInquiry}', [AdminController::class, 'updateBusinessInquiry'])->name('admin.business.update');
    Route::get('/guest-requests', [AdminController::class, 'guestRequests'])->name('admin.guest-requests.index');
    Route::patch('/guest-requests/{guestServiceRequest}', [AdminController::class, 'updateGuestRequest'])->name('admin.guest-requests.update');
});

require __DIR__.'/auth.php';
