<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{

    public function index(){
        $dateArr = $this->getDateTimeArray();
        $outputDate = $dateArr[0];
        $outputTime = $dateArr[1];
        return view('attendance', compact(['outputDate', 'outputTime']));
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

}
