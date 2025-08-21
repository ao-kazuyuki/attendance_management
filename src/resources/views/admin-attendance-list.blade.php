@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css') }}">
@endsection

@section('header-menu')
    @component('components.header-menu')
    @endcomponent
@endsection

@php
    $selectDate = new DateTime($year . '-' . $month . '-' . $day);
    $prevDate = (clone $selectDate)->modify('-1 day');
    $nextDate = (clone $selectDate)->modify('+1 day');
    $prevDateYear = $prevDate->format('Y');
    $prevDateMonth = $prevDate->format('m');
    $prevDateDay = $prevDate->format('d');
    $nextDateYear = $nextDate->format('Y');
    $nextDateMonth = $nextDate->format('m');
    $nextDateDay = $nextDate->format('d');
@endphp

@section('content')
<div class="attendance-container">
    <h1 class="main-section">{{ $year . '年' . ltrim($month, '0') . '月' . ltrim($day, '0') . '日の勤怠' }}</h1>
    <!-- 現在表示している年月日と表示日の変更ボタン -->
    <div class="change-day-area">
        <a href="{{ route('admin-select-attendance-list', ['year' => $prevDateYear, 'month' => $prevDateMonth, 'day' => $prevDateDay]) }}" class="content-group">
            <img class="arrow-img" src="{{ asset('img/arrow.png') }}">
            <span class="prev">前日</span>
        </a>
        <div class="content-group">
            <img class="calendar-img" src="{{ asset('img/calendar.png') }}">
            <h2 class="current-month">{{ $year . '/' .$month . '/' . $day }}</h2>
        </div>
        <a href="{{ route('admin-select-attendance-list', ['year' => $nextDateYear, 'month' => $nextDateMonth, 'day' => $nextDateDay]) }}" class="content-group">
            <span class="next">翌日</span>
            <img class="arrow-img rotate" src="{{ asset('img/arrow.png') }}">
        </a>
    </div>
    <!-- 勤怠一覧表 -->
    <table class="attendance-table">
        <tr><th>名前</th><th>出勤</th><th>退勤</th><th>休憩</th><th>合計</th><th>詳細</th></tr>
        @php
            foreach($works as $work){
                $dayOfStartAttendance = '';
                $dayOfFinishAttendance = '';
                $dayOfSumRestTime = '';
                $dayOfResultAttendanceTime = '';
                if(!$work->is_demand){
                    if($work->start){
                        $dayOfStartAttendance = (new DateTime($work->start))->format('H:i');
                    }
                    if($work->finish){
                        $dayOfFinishAttendance = (new DateTime($work->finish))->format('H:i');
                    }
                }else{
                    if($work->correction_start){
                        $dayOfStartAttendance = (new DateTime($work->correction_start))->format('H:i');
                    }
                    if($work->correction_finish){
                        $dayOfFinishAttendance = (new DateTime($work->correction_finish))->format('H:i');
                    }
                }
                if(isset($rests[$work->id])){
                    $dayOfSumRestTime = $rests[ $work->id ];
                }
                if(isset($resultAttendanceTime[$work->id])){
                    $dayOfResultAttendanceTime = $resultAttendanceTime[$work->id];
                }
                echo "<tr><td>" . $work->user->name  . "</td>";
                echo "<td>" . $dayOfStartAttendance . "</td>";
                echo "<td>" . $dayOfFinishAttendance . "</td>";
                echo "<td>" . $dayOfSumRestTime . "</td>";
                echo "<td>" . $dayOfResultAttendanceTime . "</td>";
                echo "<td>" . '<a class="detail-link" href="' . route('show-detail-attendance', ['id' => $work->id ?? 0]) . '">詳細</a></td></tr>';
            }
        @endphp
    </table>
</div>
@endsection