<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function showLogin(){
        return view('auth.admin-login');
    }

    public function login(AdminLoginRequest $request){
        $user = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];
        if(Auth::attempt($user, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('admin-show-attendance-list');
        }
        throw ValidationException::withMessages([
            'email' => __('ログイン情報が登録されていません'),
        ]);        
    }

    public function showAttendanceList(Request $request){
        dd('管理者としてログインしました！！');
    }
}