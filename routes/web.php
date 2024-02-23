<?php

use App\Events\OperatorMail;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Restaurant\MenuController;
use App\Http\Controllers\Restaurant\OperatoreController;
use App\Http\Controllers\Restaurant\QrCodeGeneratorController;
use App\Http\Controllers\Restaurant\RestaurantController;
use App\Http\Controllers\Restaurant\SubscriptionController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/dashboard', function () {
    if (Auth::user()->hasRole('admin')) {

        return redirect()->route('admin.dashboard');
    } elseif (Auth::user()->hasRole('restaurant owner') || Auth::user()->hasRole('operator')) {
        return redirect()->route('restaurant.dashboard');
    } else {
        return redirect()->route('login');
    }
})->name('dashboard');

Route::get('/send', function () {
    Event::dispatch(new OperatorMail('operator@gmail.cm'));
    return 'sent';
});

// Routes for restaurant owners & operators
Route::middleware(['auth', 'verified', 'checkrole:restaurant owner|operator'])->prefix('restaurant')->group(function () {
    Route::get('/',  [RestaurantController::class, 'dashboard'])->name('restaurant.dashboard');
    Route::resource('menus', MenuController::class)->except(['show']);
    Route::get('menus/{menu}', [MenuController::class, 'show'])->name('menus.show');
    Route::resource('subscriptions', SubscriptionController::class);
    Route::resource('operatores', OperatoreController::class)->except(['show']);
    Route::get('operatores/{operatore}', [OperatoreController::class, 'show'])->name('operatores.show');
    Route::post('/generate-qrcode', [QrCodeGeneratorController::class, 'generateWithColors'])->name('generate-qrcode');

    Route::get('qrcode', [QrCodeGeneratorController::class, 'generate'])->name('restaurant.qrcode');
});





// Routes for admins
Route::middleware(['auth', 'verified', 'checkrole:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::resource('plans', PlanController::class)->except(['show']);
    Route::get('plans/{plan}', [PlanController::class, 'show'])->name('plans.show');
});




// this route unauthorized
Route::get('/not-authorized', function () {
    return 'You are not authorized to access this page.';
})->name('not.authorized');



// this route for non route



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::fallback(fn () => 'not found page');



require __DIR__ . '/auth.php';
