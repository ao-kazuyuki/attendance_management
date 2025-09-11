<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorrectionRequest;
use App\Models\Demand;
use App\Models\Rest;
use App\Models\Status;
use App\Models\Work;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{

    public function index(){
        $user = Auth::user();
        //今日の勤怠レコードが存在しない場合は「勤務外」に設定
        $date = new DateTime();
        if(!$user->isWork($date)){
            $user->updateStatusByUserId('1');
            $user->load('status');
        }
        //現在の日付と時刻、勤怠状況を出力
        $outputDate = $this->getCurrentDate($date);
        $outputTime = $this->getCurrentTime($date);
        $statuses = Status::all();
        return view('attendance', compact(['user', 'outputDate', 'outputTime', 'statuses']));
    }

    public function getCurrentDate(DateTime $date = new DateTime()):string{
        $year = $date->format('Y');
        $monthDay = $date->format('n月j日');
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $week[$date->format('w')];
        $outputDate = "{$year}年{$monthDay}({$weekday})";
        return $outputDate;
    }

    public function getCurrentTime(DateTime $date = new DateTime()):string{
        $hour = $date->format('H');
        $minute = $date->format('i');
        $outputTime = "{$hour}:{$minute}";
        return $outputTime;
    }

    public function getCurrentDateTime(){
        $date = new DateTime();
        return response()->json([
            'outputDate' => $this->getCurrentDate($date),
            'outputTime' => $this->getCurrentTime($date),
        ]);
    }

    public function start(){
        $user = Auth::user();
        $user->updateStatusByUserId('2');
        $user->load('status');
        $work = $user->setStartAttendance();
        return redirect('/attendance')->with('message', '出勤しました！');
    }

    public function finish(){
        $user = Auth::user();
        $user->updateStatusByUserId('4');
        $user->load('status');
        $user->setFinishAttendance();
        return redirect('/attendance')->with('message', '退勤しました！');
    }

    public function restIn(){
        $user = Auth::user();
        $user->updateStatusByUserId('3');
        $user->load('status');
        $user->setStartRest();
        return redirect('/attendance')->with('message', '休憩入りしました！');
    }

    public function restOut(){
        $user = Auth::user();
        $user->updateStatusByUserId('2');
        $user->load('status');
        $user->setFinishRest();
        return redirect('/attendance')->with('message', '休憩を終えました！');
    }

    /**
     *  今月の勤怠一覧画面を出力します。
     */
    public function showAttendanceList(){
        $date = new DateTime();
        return $this->showAttendanceListMain($date);
    }

    /**
     *  指定した年月で勤怠一覧画面を出力します。
     */
    public function selectAttendanceList($year, $month){
        $date = new DateTime($year . '-' . $month . '-1');
        return $this->showAttendanceListMain($date);
    }

    /**
     *  今月または指定した年月で勤怠一覧画面を出力します。
     */
    public function showAttendanceListMain(DateTime $date){
        //出力年月の初日から月末までの勤怠レコードを取得
        $user = Auth::user();
        $year = $date->format('Y');
        $month = $date->format('m');
        $startDate = (clone $date)->modify('first day of this month');
        $finishDate = (clone $date)->modify('last day of this month');
        $works = $user->getWorksBetween($startDate, $finishDate);
        //各日の勤怠レコード及び休憩レコードがあれば労働時間と休憩時間を計算して配列に格納
        $resultAttendanceTime = [];
        $rests = [];
        foreach($works as $work){
            //複数回の休憩時間の合計を'0:00'形式で記憶
            $sumRestSeconds = 0;
            foreach($work->rests as $rest){
                if(!$work->is_demand || $work->demand->status == '承認待ち'){
                    if(!is_null($rest->start) && !is_null($rest->finish)){
                        $sumRestSeconds += $rest->finish->diffInSeconds($rest->start);
                    }
                }else{
                    if(!is_null($rest->correction_start) && !is_null($rest->correction_finish)){
                        $sumRestSeconds += $rest->correction_finish->diffInSeconds($rest->correction_start);
                    }
                }
            }
            $restHours = floor($sumRestSeconds / 3600);
            $restMinutes = sprintf('%02d', floor(($sumRestSeconds % 3600) / 60));
            $rests[$work->id] = "{$restHours}:{$restMinutes}";
            //休憩時間を差し引いたその日の労働時間を'0:00'形式で記憶
            if(!$work->is_demand || $work->demand->status == '承認待ち'){
                $attendanceTime = $work->getAttendanceTime();
            }else{
                $attendanceTime = $work->getAttendanceCorrectionTime();
            }
            if($attendanceTime != 0){
                $totalAttendanceTime = $attendanceTime - $sumRestSeconds;
                $totalAttendanceHours = floor($totalAttendanceTime / 3600);
                $totalAttendanceMinutes = sprintf('%02d', floor(($totalAttendanceTime % 3600) / 60));
                $resultAttendanceTime[$work->id] = "{$totalAttendanceHours}:{$totalAttendanceMinutes}";
            }else{
                $resultAttendanceTime[$work->id] = "0:00";
            }
        }
        return view('attendance-list', compact(['year', 'month', 'startDate', 'finishDate', 'works', 'rests', 'resultAttendanceTime']));
    }

    public function showDetailAttendance($id){
        $user = Auth::user();
        $work = Work::with(['demand'])->find($id);
        $rests = Rest::where('user_id', $user->id)->where('work_id', $work->id)->get();
        $restCount = $rests->count();
        if(!$work->is_demand){
            return view('attendance-detail', compact(['user', 'work', 'rests', 'restCount']));
        }else{
            $demand = $work->demand;
            return view('attendance-detail', compact(['user', 'work', 'rests', 'restCount', 'demand']));
        }
    }

    public function storeCorrection($id, CorrectionRequest $request){
        $user = Auth::user();
        $work = Work::with('rests')->find($id);
        $work->update([
            'correction_start' => $request['start-work'],
            'correction_finish' => $request['finish-work'],
            'is_demand' => true,
        ]);
        $demand = Demand::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'content' => $request['remarks'],
            'request_day' => new DateTime(),
            'status' => '承認待ち',
        ]);
        $restStartArray = $request->input('start-rest');
        $restFinishArray = $request->input('finish-rest');
        $restIdx = 0;
        foreach($work->rests as $rest){
            $rest->update([
                'correction_start' => $restStartArray[$restIdx],
                'correction_finish' => $restFinishArray[$restIdx],
            ]);
            $restIdx++;
        }
        if(!is_null($request['add-start-rest']) && !is_null($request['add-finish-rest'])){
            Rest::create([
                'user_id' => $user->id,
                'work_id' => $work->id,
                'rest_day' => $work->work_day,
                'correction_start' => $request['add-start-rest'],
                'correction_finish' => $request['add-finish-rest'],
            ]);
        }
        return redirect(route('show-detail-attendance', ['id' => $id]));
    }

    public function showCorrectionList(Request $request){
        $user = Auth::user();
        if($request->page == 'wait' || $request->page == ''){
            $demands = Demand::with(['user', 'work'])->where('user_id', $user->id)->where('status', '承認待ち')->get();
        }else if($request->page == 'approval'){
            $demands = Demand::with(['user', 'work'])->where('user_id', $user->id)->where('status', '承認済み')->get();
        }
        return view('attendance-correction', compact('request', 'user', 'demands'));
    }

}