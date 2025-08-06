@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<form class="form-group" novalidate action="/login" method="post">
    @csrf
    <h1 class="login-form__title">ログイン</h1>
    <div class="section-group">
        <h2 class="login-form__section">メールアドレス</h2>
        <input class="login-form__input" type="email" name="email" value="{{ old('email') }}">
        <div class="form-error">
            @error('email')
                {{ $message }}
            @enderror
        </div>
    </div>
    <div class="section-group">
        <h2 class="login-form__section">パスワード</h2>
        <input class="login-form__input" type="password" name="password">
        <div class="form-error">
            @error('password')
                {{ $message }}
            @enderror
        </div>
    </div>
    <input class="form-button" type="submit" value="ログインする">
    <a class="form-link" href="/register">会員登録はこちら</a>
</form>
@endsection