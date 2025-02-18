    <?php

    use App\Http\Controllers\ProfileController;
    use App\Http\Controllers\RoomController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\AdminController;
    use App\Http\Controllers\HomeController;
    use App\Http\Controllers\ReservationController;


    Route::get('/', function () {
        return view('home.welcome');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');})->middleware(['auth', 'verified'])->name('dashboard');


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
    });


    Route::middleware('auth')->group(function(){
    Route::get('/home/dashboard', [HomeController::class, 'HomeDashboard'])->name('home.dashboard');
    Route::get('/home/logout', [HomeController::class, 'HomeLogout'])->name('home.logout');


    });

    Route::get('/home/facilities', function () {
        return view('home.facilities');
    })->name('home.facilities');


  
    Route::resource('reservations', ReservationController::class);

Route::get('reservations/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
Route::get('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');

Route::get('/rooms/{room}/reserve', [ReservationController::class, 'create'])->name('reservations.create');
    

   

    Route::get('/our-rooms', [RoomController::class, 'OurRooms'])-> name('our.room');





