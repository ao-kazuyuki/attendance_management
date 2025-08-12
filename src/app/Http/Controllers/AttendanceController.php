<?php

namespace App\Http\Controllers;

use App\Models\Rest;
use App\Models\Status;
use App\Models\User;
use App\Models\Work;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{

    public function index(){
        $user = Auth::user();
        //今日の勤怠レコードが存在しない場合は「勤務外」に設定
        if(!$user->isWorkToday()){
            $user->updateStatusByUserId('1');
        }
        //現在時刻と勤怠ステータスを出力
        $dateArr = $this->getDateTimeArray();
        $outputDate = $dateArr[0];
        $outputTime = $dateArr[1];
        $statuses = Status::all();
        return view('attendance', compact(['user', 'outputDate', 'outputTime', 'statuses']));
    }

    public function start(){
        $user = Auth::user();
        $user->updateStatusByUserId('2');
        $works = $user->setStartAttendance();
        return redirect('/attendance')->with('message', '出勤しました！');
    }

    public function finish(){
        $user = Auth::user();
        $user->updateStatusByUserId('4');
        $works = $user->setFinishAttendance();
        return redirect('/attendance')->with('message', '退勤しました！');
    }

    public function restIn(){
        $user = Auth::user();
        $user->updateStatusByUserId('3');
        $rest = $user->setStartRest();
        return redirect('/attendance')->with('message', '休憩入りしました！');
    }

    public function restOut(){
        $user = Auth::user();
        $user->updateStatusByUserId('2');
        $rest = $user->setFinishRest();
        return redirect('/attendance')->with('message', '休憩を終えました！');
    }

    public function currentTime(){
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

    /**
     *  勤怠一覧画面を出力します。
     */
    public function showAttendanceList(){
        $user = Auth::user();
        //今日が所属する年と月を出力年月として指定
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        //出力月の初日から月末までの勤務記録を取得
        $startDate = (clone $date)->modify('first day of this month');
        $finishDate = (clone $date)->modify('last day of this month');
        $works = $user->getWorksBetween($startDate, $finishDate);
        $resultAttendanceTime = [];
        //各出勤日に対応する休憩レコードがあれば同一の勤務の休憩を合算して取得
        $rests = [];
        foreach($works as $work){
            $attendanceTime = $work->getAttendanceTime();
            $sumRestSeconds = 0;
            foreach($work->rests as $rest){
                $restIn = $rest->start;
                $restOut = $rest->finish;
                $sumRestSeconds += $restOut->diffInSeconds($restIn);
            }
            $restHours = floor($sumRestSeconds / 3600);
            $restMinutes = sprintf('%02d', floor(($sumRestSeconds % 3600) / 60));
            $rests[$work->id] = "{$restHours}:{$restMinutes}";
            $totalAttendanceTime = $attendanceTime - $sumRestSeconds;
            $totalAttendanceHours = floor($totalAttendanceTime / 3600);
            $totalAttendanceMinutes = sprintf('%02d', floor(($totalAttendanceTime % 3600) / 60));
            $resultAttendanceTime[$work->id] = "{$totalAttendanceHours}:{$totalAttendanceMinutes}";
        }
        return view('attendance-list', compact([
                                        'year',
                                        'month',
                                        'startDate',
                                        'finishDate',
                                        'works',
                                        'rests',
                                        'resultAttendanceTime',
        ]));
    }
}
