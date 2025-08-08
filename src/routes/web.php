<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function(){
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

Route::controller(AttendanceController::class)->group(function(){
    Route::middleware(['auth', 'verified'])->group(function(){
        Route::get('/', 'index');
    });
});

//認証誘導画面
Route::get('/email/verify', function(){
    return view('auth.induction-email');
})->name('verification.notice');

//認証メール再送信
Route::post('/email/verification-notification', function(Request $requet){
    session()->get('unauthenticated_user')->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送信しました！');
})->name('verification.send');

//認証処理とリダイレクト
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/');
})->name('verification.verify');