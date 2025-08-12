<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::controller(AttendanceController::class)->group(function(){
    Route::middleware(['auth', 'verified'])->group(function(){
        Route::get('/attendance', 'index')->name('show-attendance');         //打刻画面
        Route::post('/attendance/start', 'start')->name('start');            //出勤処理
        Route::post('/attendance/finish', 'finish')->name('finish');         //退勤処理
        Route::post('/attendance/rest-in', 'restIn')->name('rest-in');       //休憩(入)処理
        Route::post('/attendance/rest-out', 'restOut')->name('rest-out');    //休憩(戻)処理
        Route::get('/api/current-time', 'currentTime');                      //非同期に時間を取得
        Route::get('/attendance/list', 'showAttendanceList')->name('show-attendance-list');

        Route::get('/attendance/list/{year}/{month}', 'selectAttendanceList')->name('select-attendance-list');

    });
});

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

//メール認証誘導画面
Route::get('/email/verify', function(){
    return view('auth.induction-email');
})->name('verification.notice');

//認証メール再送信
Route::post('/email/verification-notification', function(Request $requet){
    session()->get('unauthenticated_user')->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送信しました！');
})->name('verification.send');

//認証処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/attendance');
})->name('verification.verify');