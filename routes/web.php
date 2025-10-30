    <?php

    use App\Http\Controllers\ProfileController;
    use App\Http\Controllers\RoomController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\AdminController;
    use App\Http\Controllers\ReservationController;
    use App\Http\Controllers\HomeController;
use App\Http\Controllers\WatchDog;

    Route::get('/', function () {
        return view('home.welcome');
    });

    Route::get('/home/dashboard', function () {
        return view('home.dashboard');})->middleware(['auth', 'verified'])->name('DASHBOARD');


    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';

    Route::middleware(['auth', 'role:admin']    )->group(function(){
    Route::get('/admin/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');
    Route::get('/admin/logout', [AdminController::class, 'AdminLogout' ])->name('admin.logout');
    Route::get('/admin/profile', [AdminController::class, 'AdminProfile']);
    Route::post('profile/update', [AdminController::class, 'AdminProfileUpdate']);
    Route::resource('/admin/rooms', RoomController::class)->names('rooms');
    Route::get('/admin/reservations', [AdminController::class, 'AdminReservation'])->name('admin.reservation');
    Route::post('admin/updateReservation/{id}', [AdminController::class, 'UpdateReservation'])->name('update.reservation');
    });


    Route::middleware('auth')->group(function(){
    Route::get('/home/dashboard', [HomeController::class, 'HomeDashboard'])->name('home.dashboard');
    Route::get('/home/logout', [HomeController::class, 'HomeLogout'])->name('home.logout');


    });

    Route::get('/home/facilities', function () {
        return view('home.facilities');
    })->name('home.facilities');

    Route::get('/our-rooms', [RoomController::class, 'OurRooms'])-> name('our.room');




    Route::middleware('auth')->group(function() {
        Route::get('/receipt/{id}', [ReservationController::class, 'showReceipt'])->name('receipt');
        Route::get('/booking/{id}', [ReservationController::class, 'bookingForm'])->name('booking.form');
        Route::post('/booking', [ReservationController::class,'store'])->name('booking.store');


    });

    Route::get('test-queue', [WatchDog::class, 'testQueue'])->name('test-queue');
    
