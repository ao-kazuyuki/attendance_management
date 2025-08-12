@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('header-menu')
    @component('components.header-menu')
    @endcomponent
@endsection

@section('content')
<div class="attendance-container">
    <h1 class="main-section">勤怠一覧</h1>
    <!-- 現在表示している年月と表示月の変更ボタン -->
    <div class="change-month-area">
        <div class="content-group">
            <img class="arrow-img" src="{{ asset('img/arrow.png') }}">
            <span class="prev">前月</span>
        </div>
        <div class="content-group">
            <img class="calendar-img" src="{{ asset('img/calendar.png') }}">
            <h2 class="current-month">{{ $year . '/' .$month }}</h2>
        </div>
        <div class="content-group">
            <span class="next">翌月</span>
            <img class="arrow-img rotate" src="{{ asset('img/arrow.png') }}">
        </div>
    </div>
    <!-- 勤怠一覧表 -->
    <table class="attendance-table">
        <tr>
            <th>日付</th><th>出勤</th><th>退勤</th><th>休憩</th><th>合計</th><th>詳細</th>
        </tr>
        @php

            //月初めの曜日を設定
            $week = ['日', '月', '火', '水', '木', '金', '土'];
            $weekdayNum = $startDate->format('w');

            //月末の最終日を取得
            $lastDayOfCurrentMonth = $finishDate->format('j');

            //各勤怠レコードを日付をkeyとする連想配列としてセット
            $worksByDate = [];
            foreach($works as $work){
                $key = (new DateTime($work->start))->format('Y-m-d');
                $worksByDate[$key] = $work;
            }

            //勤怠記録をテーブルに出力
            for($day=1; $day<=$lastDayOfCurrentMonth; $day++){

                //最終日以外のtdデザイン
                if($day != $lastDayOfCurrentMonth){
                    $tdStyle = '<td>';
                    $detailTdStyle = '<td class="detail">';

                //最終日のtdデザイン
                }else{
                    $tdStyle = '<td style="border-bottom:none;">';
                    $detailTdStyle = '<td class="detail" style="border-bottom:none;">';
                }

                //日付
                echo "<tr>" . $tdStyle . $month . '/' . sprintf('%02d', $day) . '(' . $week[ $weekdayNum ] . ")</td>";

                //該当日の勤務レコードがあるか調べ、出勤時間、退勤時間、休憩時間を出力
                $dateObj = (clone $startDate)->setDate($year, $month, $day);
                $dateKey = $dateObj->format('Y-m-d');
                $work = $worksByDate[$dateKey] ?? null;
                $dayOfStartAttendance = '';
                $dayOfFinishAttendance = '';
                $dayOfSumRestTime = '0:00';
                $dayOfResultAttendanceTime = '';

                if($work){
                    if($work->start){
                        $dayOfStartAttendance = (new DateTime($work->start))->format('H:i');
                    }
                    if($work->finish){
                        $dayOfFinishAttendance = (new DateTime($work->finish))->format('H:i');
                    }
                    if(isset($rests[$work->id])){
                        $dayOfSumRestTime = $rests[ $work->id ];
                    }
                    if(isset($resultAttendanceTime[$work->id])){
                        $dayOfResultAttendanceTime = $resultAttendanceTime[$work->id];
                    }
                }
                echo $tdStyle . $dayOfStartAttendance . "</td>";
                echo $tdStyle . $dayOfFinishAttendance . "</td>";
                echo $tdStyle . $dayOfSumRestTime . "</td>";
                echo $tdStyle . $dayOfResultAttendanceTime . "</td>";


                echo $detailTdStyle . "詳細</td></tr>";
                $weekdayNum++;
                if($weekdayNum > 6){
                    $weekdayNum = 0;
                }
            }
        @endphp
    </table>
</div>
@endsection