<?php

use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/register', [UserController::class, 'Register']);
Route::post('/login', [UserController::class, 'Login']);



Route::group(['middleware'=> ['auth:sanctum']], function () {
    
    Route::post('/create_event', [EventController::class, 'create_event']);
    Route::post('/register_for_event', [EventController::class, 'register_for_event']);
    Route::get('/get_all_created_events', [EventController::class, 'get_all_created_events']);
    Route::get('/get_all_registered_events', [EventController::class, 'get_all_registered_events']);
    Route::get('/search_events/{search}', [EventController::class, 'search_events']);
    Route::get('/get_all_events', [EventController::class, 'get_all_events']);


    

    

    
    

    Route::post('/logout', [UserController::class, 'Logout']);
});