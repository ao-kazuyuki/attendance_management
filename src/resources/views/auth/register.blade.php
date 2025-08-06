@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<form class="form-group" novalidate action="/register" method="post">
    @csrf
    <h1 class="register-form__title">会員登録</h1>
    <div class="section-group">
        <h2 class="register-form__section">名前</h2>
        <input class="register-form__input" type="text" name="name" value="{{ old('name') }}">
        <div class="form-error">
            @error('name')
                {{ $message }}
            @enderror
        </div>
    </div>
    <div class="section-group">
        <h2 class="register-form__section">メールアドレス</h2>
        <input class="register-form__input" type="email" name="email" value="{{ old('email') }}">
        <div class="form-error">
            @error('email')
                {{ $message }}
            @enderror
        </div>
    </div>
    <div class="section-group">
        <h2 class="register-form__section">パスワード</h2>
        <input class="register-form__input" type="password" name="password">
        <div class="form-error">
            @error('password')
                {{ $message }}
            @enderror
        </div>
    </div>
    <div class="section-group">
        <h2 class="register-form__section">パスワード確認</h2>
        <input class="register-form__input" type="password" name="password_confirmation">
        <div class="form-error">
            @error('password_confirmation')
                {{ $message }}
            @enderror
        </div>
    </div>
    <input class="form-button" type="submit" value="登録する">
    <a class="form-link" href="/login">ログインはこちら</a>
</form>
@endsection