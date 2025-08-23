@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-detail.css') }}">
@endsection

@section('header-menu')
    @component('components.header-menu')
    @endcomponent
@endsection

@php
    $year = $work->work_day->format('Y') . '年';
    $monthDay = $work->work_day->format('n') . '月' . $work->work_day->format('d') . '日';
    $additionRest = $restCount + 1;
    if(!$work->is_demand){
        $startWork = $work->start->format('H') . ':' . $work->start->format('i');
        $finishWork = $work->finish->format('H') . ':' . $work->finish->format('i');
        $remarks = '';
        if(old('remarks')){
            $remarks = old('remarks');
        }
    }else{
        $startWork = $work->correction_start->format('H') . ':' . $work->correction_start->format('i');
        $finishWork = $work->correction_finish->format('H') . ':' . $work->correction_finish->format('i');
        $remarks = $demand->content;
    }
@endphp

@section('content')
<div class="attendance-container">
    <h1 class="main-section">勤怠詳細</h1>
    <form action="{{ route('admin-correction-request', ['id' => $work->id] ) }}" method="post">
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
                @php
                    if(!$work->is_demand){
                        echo '<td><input class="input-time" type="text" name="start-work" value="' . old('start-work', $startWork ?? '' ) .'"></td>';
                        echo '<td class="period">～</td>';
                        echo '<td><input class="input-time" type="text" name="finish-work" value="' . old('finish-work', $finishWork ?? '') . '"></td></tr>';
                        if( $errors->has('start-work') || $errors->has('finish-work') ){
                            echo '<tr style="height:30px;"><td style="padding-left:340px;" colspan="4"><div class="form-error">';
                            echo '出勤時間もしくは退勤時間が不適切な値です';
                            echo '</div></td></tr>';
                        }
                    }else{
                        echo '<td><div class="input-time">' . $startWork . '</div></td>';
                        echo '<td class="period">～</td>';
                        echo '<td><div class="input-time">' . $finishWork . '</div></td></tr>';
                    }
                @endphp
            <!-- 休憩 -->
            @php
                for($i=1; $i<=$restCount; $i++){
                    $sectionTitle = "休憩";
                    if($i != 1){
                        $sectionTitle .= $i;
                    }
                    $rest = $rests[$i - 1];
                    if(!$work->is_demand){
                        $startRest = old('start-rest.' . ($i - 1), $rest->start->format('H') . ':' . $rest->start->format('i'));
                        $finishRest = old('finish-rest.' . ($i - 1), $rest->finish->format('H') . ':' . $rest->finish->format('i'));
                        echo '<tr><th>' . $sectionTitle . '</th>';
                        echo '<td><input class="input-time" type="text" name="start-rest[' . ($i - 1) . ']" value="' . $startRest . '"></td>';
                        echo '<td class="period">～</td>';
                        echo '<td><input class="input-time" type="text" name="finish-rest[' . ($i - 1) . ']" value="' . $finishRest . '"></td></tr>';
                        if( $errors->has('start-rest.' . ($i - 1)) || $errors->has('finish-rest.' . ($i - 1))){
                            echo '<tr style="height:30px;"><td style="padding-left:340px;" colspan="4"><div class="form-error">';
                            echo '休憩時間が不適切な値です';
                            echo '</div></td></tr>';
                        }
                    }else{
                        $startRest = $rest->correction_start->format('H') . ':' . $rest->correction_start->format('i');
                        $finishRest = $rest->correction_finish->format('H') . ':' . $rest->correction_finish->format('i');
                        echo '<tr><th>' . $sectionTitle . '</th>';
                        echo '<td><div class="input-time">' . $startRest . '</div></td>';
                        echo '<td class="period">～</td>';
                        echo '<td><div class="input-time">' . $finishRest . '</div></td></tr>';
                    }
                }
                //追加の休憩
                if(!$work->is_demand){
                    echo '<tr><th>休憩' . $additionRest . '</th><td><input class="input-time" type="text" name="add-start-rest" value="' . old('add-start-rest') . '"></td>';
                    echo '<td class="period">～</td>';
                    echo '<td><input class="input-time" type="text" name="add-finish-rest" value="' . old('add-finish-rest') . '"></td></tr>';
                    if( $errors->has('add-start-rest') || $errors->has('add-finish-rest')){
                        echo '<tr style="height:30px;"><td style="padding-left:340px;" colspan="4"><div class="form-error">';
                        echo '休憩時間が不適切な値です';
                        echo '</div></td></tr>';
                    }
                }
            @endphp
            <!-- 備考 -->
            <tr>
                <th>備考</th>
                @php
                    if(!$work->is_demand){
                        echo '<td colspan="3"><textarea name="remarks" class="remarks" >' . $remarks . '</textarea></td>';
                        if( $errors->has('remarks')){
                            echo '<tr style="height:30px;"><td style="padding-left:340px;" colspan="4"><div class="form-error">';
                            echo  $errors->first('remarks');
                            echo '</div></td></tr>';
                        }
                    }else{
                        echo '<td colspan="3"><div class="remarks-non-edit">' . $remarks . '</div></td>';
                    }
                @endphp
            </tr>
        </table>
        <div class="button-layout">
            @php
                if(!$work->is_demand){
                    echo '<button class="correction-btn">修正</button>';
                }else{
                    if($demand->status == '承認待ち'){
                        echo '<div class="attention">*承認待ちのため修正はできません。</div>';
                    }else if($demand->status == '承認済み'){
                        echo '<div class="approved">承認済み</div>';
                    }
                }
            @endphp
        </div>
    </form>
</div>
@endsection