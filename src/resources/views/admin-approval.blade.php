@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-approval.css') }}">
@endsection

@section('header-menu')
    @component('components.header-menu')
    @endcomponent
@endsection

@php
    $year = $work->work_day->format('Y') . '年';
    $monthDay = $work->work_day->format('n') . '月' . $work->work_day->format('d') . '日';
    $startWork = $work->correction_start->format('H') . ':' . $work->correction_start->format('i');
    $finishWork = $work->correction_finish->format('H') . ':' . $work->correction_finish->format('i');
    $remarks = $demand->content;
@endphp

@section('content')
<div class="attendance-container">
    <h1 class="main-section">勤怠詳細</h1>
    <form action="{{ route('admin-approval-request', ['attendance_correct_request' => $work->id] ) }}" method="post">
        @csrf
        <table class="detail-table">
            <!-- 名前 -->
            <tr>
                <th>名前</th>
                <td colspan="3">{{ $user->name }}</td>
            </tr>
            <!-- 日付 -->
            <tr>
                <th>日付</th>
                <td style="padding-left:65px;" colspan="2">{{ $year }}</td>
                <td class="left-padding">{{ $monthDay }}</td>
            </tr>
            <!-- 出勤・退勤 -->
            <tr>
                <th>出勤・退勤</th>
                <td><div class="input-time">{{ $startWork }}</div></td>
                <td class="period">～</td>
                <td><div class="input-time">{{ $finishWork }}</div></td></tr>
            <!-- 休憩 -->
            @php
                for($i=1; $i<=$restCount; $i++){
                    $sectionTitle = "休憩";
                    if($i != 1){
                        $sectionTitle .= $i;
                    }
                    $rest = $rests[$i - 1];
                    $startRest = $rest->correction_start->format('H') . ':' . $rest->correction_start->format('i');
                    $finishRest = $rest->correction_finish->format('H') . ':' . $rest->correction_finish->format('i');
                    echo '<tr><th>' . $sectionTitle . '</th>';
                    echo '<td><div class="input-time">' . $startRest . '</div></td>';
                    echo '<td class="period">～</td>';
                    echo '<td><div class="input-time">' . $finishRest . '</div></td></tr>';
                }
            @endphp
            <!-- 備考 -->
            <tr>
                <th>備考</th>
                <td colspan="3"><div class="remarks-non-edit">{{ $remarks }}</div></td>
            </tr>
        </table>
        <div class="button-layout">
            @php
                if($demand->status == '承認待ち'){
                    echo '<button class="correction-btn">承認</button>';
                }else if($demand->status == '承認済み'){
                    echo '<div class="approved">承認済み</div>';
                }
            @endphp
        </div>
    </form>
</div>
@endsection