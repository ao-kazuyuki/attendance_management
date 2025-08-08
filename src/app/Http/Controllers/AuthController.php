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
        $user = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];
        if(Auth::attempt($user, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }
        throw ValidationException::withMessages([
            'email' => __('ログイン情報が登録されていません'),
        ]);        
    }

    public function logout( Request $request ){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

}