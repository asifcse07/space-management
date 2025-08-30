<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiController;

Route::get('/ai', [AiController::class, 'index']);
Route::post('/ai/chat', [AiController::class, 'chat']);
