<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;
require base_path('routes/ai.php');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
