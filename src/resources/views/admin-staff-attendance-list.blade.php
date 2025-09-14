@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-staff-attendance-list.css') }}">
@endsection

@section('header-menu')
    @component('components.header-menu')
    @endcomponent
@endsection

@php
    $selectDate = new DateTime($year . '-' . $month . '-1');
    $prevDate = (clone $selectDate)->modify('first day of previous month');
    $nextDate = (clone $selectDate)->modify('first day of next month');
    $prevDateYear = $prevDate->format('Y');
    $prevDateMonth = $prevDate->format('m');
    $nextDateYear = $nextDate->format('Y');
    $nextDateMonth = $nextDate->format('m');
@endphp

@section('content')
<div class="attendance-container">
    <h1 class="main-section">{{ $user->name . 'さんの勤怠' }}</h1>
    <!-- 現在表示している年月と表示月の変更ボタン -->
    <div class="change-month-area">
        <a href="{{ route('select-staff-attendance-list', ['id' => $user->id, 'year' => $prevDateYear, 'month' => $prevDateMonth]) }}" class="content-group">
            <img class="arrow-img" src="{{ asset('img/arrow.png') }}">
            <span class="prev">前月</span>
        </a>
        <div class="content-group">
            <img class="calendar-img" src="{{ asset('img/calendar.png') }}">
            <h2 class="current-month">{{ $year . '/' .$month }}</h2>
        </div>
        <a href="{{ route('select-staff-attendance-list', ['id' => $user->id, 'year' => $nextDateYear, 'month' => $nextDateMonth]) }}" class="content-group">
            <span class="next">翌月</span>
            <img class="arrow-img rotate" src="{{ asset('img/arrow.png') }}">
        </a>
    </div>
    <!-- 勤怠一覧表 -->
    <table class="attendance-table">
        <tr><th>日付</th><th>出勤</th><th>退勤</th><th>休憩</th><th>合計</th><th>詳細</th></tr>
        @php
            //月初めの曜日を設定
            $week = ['日', '月', '火', '水', '木', '金', '土'];
            $weekdayNum = $startDate->format('w');
            //月末の最終日を取得
            $lastDayOfCurrentMonth = $finishDate->format('j');
            //各勤怠レコードを日付をkeyとする連想配列としてセット
            $worksByDate = [];
            foreach($works as $work){
                $key = (new DateTime($work->work_day))->format('Y-m-d');
                $worksByDate[$key] = $work;
            }
            //月初めから月末まで各日の勤務時間などを出力
            for($day=1; $day<=$lastDayOfCurrentMonth; $day++){
                $dateObj = (clone $startDate)->setDate($year, $month, $day);
                $dateKey = $dateObj->format('Y-m-d');
                $work = $worksByDate[$dateKey] ?? null;
                $dayOfStartAttendance = '';
                $dayOfFinishAttendance = '';
                $dayOfSumRestTime = '';
                $dayOfResultAttendanceTime = '';
                if($work){
                    if(!$work->is_demand || $work->demand->status == '承認待ち'){
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
                }
                echo "<tr><td>" . $month . '/' . sprintf('%02d', $day) . '(' . $week[ $weekdayNum ] . ")</td>";
                echo "<td>" . $dayOfStartAttendance . "</td>";
                echo "<td>" . $dayOfFinishAttendance . "</td>";
                echo "<td>" . $dayOfSumRestTime . "</td>";
                echo "<td>" . $dayOfResultAttendanceTime . "</td>";
                if(isset($work)){
                    if(!is_null($work->start) && !is_null($work->finish)){
                        echo "<td>" . '<a class="detail-link" href="' . route('admin-show-detail-attendance', ['id' => $work->id ?? 0]) . '">詳細</a></td></tr>';
                    }else{
                        echo "<td></td></tr>";                        
                    }
                }else{
                    echo "<td></td></tr>";
                }
                $weekdayNum++;
                if($weekdayNum > 6){
                    $weekdayNum = 0;
                }
            }
        @endphp
    </table>
    <form class="csv-layout" action="{{ route('admin-export-csv', ['user_id' => $user->id, 'year' => $year, 'month' => $month ]) }}" method="post">
        @csrf
        <input class="csv-btn" type="submit" value="CSV出力">
    </form>
</div>
@endsection