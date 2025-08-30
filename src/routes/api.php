<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BuildingController;

Route::apiResource('buildings', BuildingController::class);
// TODO: add FloorController, AreaController similarly
