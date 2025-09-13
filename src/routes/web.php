<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function(){
    Route::middleware('guest')->group(function(){
        Route::get('/register', 'register')->name('register');                                                                  //ユーザー登録画面表示
        Route::post('/register', 'store');                                                                                      //ユーザー登録処理
        Route::get('/login', 'showLogin')->name('login');                                                                       //ログイン画面表示
        Route::post('/login', 'login');                                                                                         //ログイン処理
    });
    Route::middleware('auth')->group(function(){
        Route::post('/logout', 'logout')->name('logout');                                                                       //ログアウト処理
    });
});

Route::controller(AttendanceController::class)->group(function(){
    Route::middleware(['auth', 'verified'])->group(function(){
        Route::get('/attendance', 'index')->name('show-attendance');                                                            //勤怠登録画面表示
        Route::post('/attendance/start', 'start')->name('start');                                                               //出勤処理
        Route::post('/attendance/finish', 'finish')->name('finish');                                                            //退勤処理
        Route::post('/attendance/rest-in', 'restIn')->name('rest-in');                                                          //休憩(入)処理
        Route::post('/attendance/rest-out', 'restOut')->name('rest-out');                                                       //休憩(戻)処理
        Route::get('/attendance/current-time', 'getCurrentDateTime')->name('current-time');                                     //非同期に時間を取得
        Route::get('/attendance/list', 'showAttendanceList')->name('show-attendance-list');                                     //勤怠一覧画面表示
        Route::get('/attendance/list/{year}/{month}', 'selectAttendanceList')->name('select-attendance-list');                  //指定年月の勤怠一覧画面表示
        Route::get('/attendance/{id}', 'showDetailAttendance')->name('show-detail-attendance');                                 //勤怠詳細画面表示
        Route::post('/attendance/{id}', 'storeCorrection')->name('correction-request');                                         //勤怠修正依頼
        Route::get('/general/stamp_correction_request/list', 'showCorrectionList')->name('general-show-correction-list');       //申請一覧画面表示
    });
});

Route::controller(AdminController::class)->group(function(){
    Route::middleware('guest')->group(function(){
        Route::get('/admin/login', 'showLogin')->name('admin-login');                                                                   //管理者ログイン画面表示
        Route::post('/admin/login', 'login');                                                                                           //管理者ログイン処理
    });
    Route::middleware('auth')->group(function(){
        Route::post('/admin/logout', 'logout')->name('admin-logout');                                                                   //管理者ログアウト処理
    });
    Route::middleware(['is_admin'])->group(function(){
        Route::get('/admin/attendance/list', 'showAttendanceList')->name('admin-show-attendance-list');                                 //管理者で勤怠一覧画面を表示
        Route::get('/admin/attendance/list/{year}/{month}/{day}', 'selectAttendanceList')->name('admin-select-attendance-list');        //管理者で指定した年月日の勤怠一覧画面を表示
        Route::get('/admin/attendance/{id}', 'showDetailAttendance')->name('admin-show-detail-attendance');                             //管理者で勤怠詳細画面を表示
        Route::post('/admin/attendance/{id}', 'storeCorrection')->name('admin-correction-request');                                     //管理者権限で勤怠の修正をかける処理
        Route::get('/admin/staff/list', 'showStaffList')->name('show-staff-list');                                                      //スタッフ一覧画面を表示
        Route::get('/admin/attendance/staff/{id}', 'showStaffAttendanceList')->name('show-staff-attendance-list');                      //スタッフ別勤怠一覧画面を表示
        Route::get('/admin/attendance/staff/{id}/{year}/{month}', 'selectStaffAttendanceList')->name('select-staff-attendance-list');   //指定した年月のスタッフ別勤怠一覧画面を表示
        Route::get('/admin/stamp_correction_request/list', 'showCorrectionList')->name('admin-show-correction-list');                   //管理者で申請一覧画面を表示
        Route::get('/stamp_correction_request/approve/{attendance_correct_request}', 'showApproval')->name('show-approval');            //管理者で修正申請承認画面を表示
        Route::post('/admin/approval/{attendance_correct_request}', 'storeApproval')->name('admin-approval-request');                   //修正申請を管理者が承認する処理
        Route::post('/admin/export/csv/{user_id}/{year}/{month}', 'exportCsv')->name('admin-export-csv');                               //勤怠情報をCSV出力する処理
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