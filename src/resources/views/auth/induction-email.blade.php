@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/induction-email.css') }}">
@endsection

@section('content')

<div class="flash-layout" id="flashLayout">
    @if(session('message'))
    <div class="flash-msg" id="flashMsg">{{ session('message') }}</div>
    @endif
</div>

<div class="guidance-layout">
    <p class="guidance-content">登録していただいたメールアドレスに認証メールを送付しました。<br>メール認証を完了してください。<p>
    <form>
        @csrf
        <button class="form-button" type="submit">認証はこちらから</button>
    </form>

    <form method="post" action="{{ route('verification.send') }}">
        @csrf
        <button class="resend-link" type="submit">認証メールを再送する</button>
    </form>
</div>

<script>
    if(document.getElementById('flashMsg')){
        window.addEventListener('load', () => {
            const flashElm = document.getElementById('flashLayout');
            flashElm.style.backgroundColor = "#00cc00";
            flashElm.classList.add('hide');
        });
    }
</script>
@endsection