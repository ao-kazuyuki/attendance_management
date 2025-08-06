<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::controller(AuthController::class)->group(function(){
    Route::get('/', 'index');
    Route::middleware('guest')->group(function(){
        Route::get('/register', 'register')->name('register');
        Route::post('/register', 'store');
        Route::get('/login', 'showLogin')->name('login');
        Route::post('/login', 'login');
    });
    Route::middleware('auth')->group(function(){
        Route::post('/logout', 'logout')->name('logout');
    });
});