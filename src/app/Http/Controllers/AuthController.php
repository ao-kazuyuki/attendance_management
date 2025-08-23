<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(){
        return view('auth.register');
    }

    public function store( RegisterRequest $request ){
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'status_id' => '1',
        ]);
        Auth::login($user);
        $user->sendEmailVerificationNotification();
        session()->put('unauthenticated_user', $user);
        return redirect()->route('verification.notice');
    }

    public function showLogin(){
        return view('auth.login');
    }

    public function login( LoginRequest $request ){
        $email = $request->input('email');
        $pw = $request->input('password');
        if(Auth::validate(['email' => $email, 'password' => $pw])){
            $user = User::where('email', '=', $email)->first();
            if($user->is_admin){
                return redirect()->route('admin-login');
            }
            $general_user = [
                'email' => $request->input('email'),
                'password' => $request->input('password'),
            ];
            if(Auth::attempt($general_user, $request->filled('remember'))) {
                $request->session()->regenerate();
                return redirect()->route('show-attendance');
            }
        }else{
            throw ValidationException::withMessages([
                'email' => __('ログイン情報が登録されていません'),
            ]);
        }
    }

    public function logout( Request $request ){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}