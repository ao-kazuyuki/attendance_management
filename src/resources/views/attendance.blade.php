@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('header-menu')
    @component('components.header-menu')
    @endcomponent
@endsection

@section('content')
<form class="attendance-layout">
    @csrf
    <div class="attendance-state">勤務外</div>
    <div class="attendance-day" id="date">{{ $outputDate }}</div>
    <div class="attendance-time" id="time">{{ $outputTime }}</div>
    <button class="attendance-btn" type="submit">出勤</button>
</form>

<!-- 現在時刻を非同期に取得 -->
<script>
    const dateElm = document.getElementById('date');
    const timeElm = document.getElementById('time');
    const updateTime = async function(){
        try{
            const res = await fetch('/api/current-time');
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