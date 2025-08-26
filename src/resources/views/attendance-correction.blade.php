@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-correction.css') }}">
@endsection

@section('header-menu')
    @component('components.header-menu')
    @endcomponent
@endsection

@section('content')
<div class="attendance-container">
    <h1 class="main-section">申請一覧</h1>
    <!-- 承認待ち・承認済み -->
    <div class="select-state-area">
        @isset($request)
            @if($request->page == 'wait' || $request->page == '')
                <h2 class="select-content selected"><a href="{{ route('general-show-correction-list', ['page' => 'wait']) }}">承認待ち</a></h2>
                <h2 class="select-content"><a href="{{ route('general-show-correction-list', ['page' => 'approval']) }}">承認済み</a></h2>
            @elseif($request->page == 'approval')
                <h2 class="select-content"><a href="{{ route('general-show-correction-list', ['page' => 'wait']) }}">承認待ち</a></h2>
                <h2 class="select-content selected"><a href="{{ route('general-show-correction-list', ['page' => 'approval']) }}">承認済み</a></h2>
            @endif
        @endisset
    </div>
    <!-- 申請一覧表 -->
    <table class="attendance-table">
        <tr><th>状態</th><th>名前</th><th>対象日時</th><th>申請理由</th><th>申請日時</th><th>詳細</th></tr>
        @php
            foreach($demands as $demand){
                echo "<tr><td>" . $demand->status . "</td>";
                echo "<td>" . $user->name . "</td>";
                echo "<td>" . $demand->work->work_day->format('Y-m-d') . "</td>";
                echo "<td>" . $demand->content . "</td>";
                echo "<td>" . $demand->request_day->format('Y-m-d') . "</td>";
                echo "<td>" . '<a class="detail-link" href="' . route('show-detail-attendance', ['id' => $demand->work->id ?? 0]) . '">詳細</a></td></tr>';
            }
        @endphp
    </table>
</div>
@endsection