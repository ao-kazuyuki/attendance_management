@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-staff-list.css') }}">
@endsection

@section('header-menu')
    @component('components.header-menu')
    @endcomponent
@endsection

@section('content')
<div class="attendance-container">
    <h1 class="main-section">スタッフ一覧</h1>
    <!-- スタッフ一覧表 -->
    <table class="attendance-table">
        <tr><th>名前</th><th>メールアドレス</th><th>月次勤怠</th></tr>
        @php
            foreach($users as $user){
                echo "<tr><td>" . $user->name  . "</td>";
                echo "<td>" . $user->email . "</td>";
                echo "<td>" . '<a class="detail-link" href="' . route('staff-attendance-list', ['id' => $user->id]) . '">詳細</a></td></tr>';
            }
        @endphp
    </table>
</div>
@endsection