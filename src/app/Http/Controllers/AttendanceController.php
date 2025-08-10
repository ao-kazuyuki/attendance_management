<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{

    public function index(){
        $user = Auth::user();
        $statuses = Status::all();
        $dateArr = $this->getDateTimeArray();
        $outputDate = $dateArr[0];
        $outputTime = $dateArr[1];
        return view('attendance', compact(['user', 'statuses', 'outputDate', 'outputTime']));
    }

    public function start(){
        $user = Auth::user();
        User::updateStatusByuserId($user->id, '2');
        return redirect('/attendance')->with('message', '出勤しました！');
    }

    public function finish(){
        $user = Auth::user();
        User::updateStatusByuserId($user->id, '4');
        return redirect('/attendance')->with('message', '退勤しました！');
    }

    public function breakIn(){
        $user = Auth::user();
        User::updateStatusByuserId($user->id, '3');
        return redirect('/attendance')->with('message', '休憩入りしました！');
    }

    public function breakOut(){
        $user = Auth::user();
        User::updateStatusByuserId($user->id, '2');
        return redirect('/attendance')->with('message', '休憩を終えました！');
    }

    public function currentTime(){
        $user = Auth::user();
        $dateArr = $this->getDateTimeArray();
        $outputDate = $dateArr[0];
        $outputTime = $dateArr[1];
        return response()->json([
            'outputDate' => $outputDate,
            'outputTime' => $outputTime,
        ]);
    }

    public function getDateTimeArray(){
        $date = new DateTime();
        $year = $date->format('Y');
        $monthDay = $date->format('n月j日');
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $week[$date->format('w')];
        $outputDate = "{$year}年{$monthDay}({$weekday})";
        $hour = $date->format('H');
        $minute = $date->format('i');
        $outputTime = "{$hour}:{$minute}";
        return [ $outputDate, $outputTime ];
    }

}
