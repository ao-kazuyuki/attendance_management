@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('header-menu')
    @component('components.header-menu')
    @endcomponent
@endsection

@section('content')

<div class="flash-layout" id="flashLayout">
@if(session('message'))
    <div class="flash-msg" id="flashMsg">{{ session('message') }}</div>
@endif
</div>

<div class="attendance-layout">
    <div class="attendance-state">{{ $user->getStatus() }}</div>
    <div class="attendance-day" id="date">{{ $outputDate }}</div>
    <div class="attendance-time" id="time">{{ $outputTime }}</div>
    @if($user->getStatus()=='勤務外')
        <form action="{{ route('start') }}" method="post">
            @csrf
            <button class="attendance-btn" type="submit">出勤</button>
        </form>
    @elseif($user->getStatus()=='出勤中')
        <div style="display:flex">
            <form action="{{ route('finish') }}" method="post" >
                @csrf
                <button class="attendance-btn" type="submit">退勤</button>
            </form>
            <form action="{{ route('rest-in') }}" method="post">
                @csrf
                <button class="attendance-btn white" type="submit">休憩入</button>
            </form>
        </div>
    @elseif($user->getStatus()=='休憩中')
        <form action="{{ route('rest-out') }}" method="post">
            @csrf
            <button class="attendance-btn white" type="submit">休憩戻</button>
        </form>
    @elseif($user->getStatus()=='退勤済')
        <span class="attendance-comment">お疲れさまでした。</span>
    @endif
</div>

<!-- 現在時刻を非同期に取得 -->
<script>
    if(document.getElementById('flashMsg')){
        window.addEventListener('load', () => {
            const flashElm = document.getElementById('flashLayout');
            flashElm.style.backgroundColor = "#00cc00";
            flashElm.classList.add('hide');
        });
    }
    const dateElm = document.getElementById('date');
    const timeElm = document.getElementById('time');
    const updateTime = async function(){
        try{
            const res = await fetch("{{route('current-time')}}");
            const data = await res.json();
            dateElm.textContent = data.outputDate;
            timeElm.textContent = data.outputTime;
        }catch(e){
            console.error('現在時間の取得に失敗しました:', e);
        }
    }
    const scheduleNextTime = function(){
        const now = new Date();
        const delay = (60 - now.getSeconds() * 1000 - now.getMilliseconds());
        setTimeout(async () => {
            await updateTime();
            scheduleNextTime();
        }, delay);
    }
    updateTime();
    scheduleNextTime();
</script>
@endsection