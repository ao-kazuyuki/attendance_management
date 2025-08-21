<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Models\Work;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function showLogin(){
        return view('auth.admin-login');
    }

    public function login(AdminLoginRequest $request){
        $user = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];
        if(Auth::attempt($user, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('admin-show-attendance-list');
        }
        throw ValidationException::withMessages([
            'email' => __('ログイン情報が登録されていません'),
        ]);        
    }

    /**
     *  本日の勤怠一覧画面を出力します。
     */
    public function showAttendanceList(Request $request){
        $date = new DateTime();
        return $this->showAttendanceListMain($date);
    }

    /**
     *  指定した日の勤怠一覧画面を出力します。
     */
    public function selectAttendanceList($year, $month, $day){
        $date = new DateTime($year . '-' . $month . '-' . $day);
        return $this->showAttendanceListMain($date);
    }

    /**
     *  本日または指定した日の勤怠一覧画面を出力します。
     */
    public function showAttendanceListMain(DateTime $date){
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');
        $works = Work::with('rests', 'user')
                    ->where('work_day', '=', $date->format('Y-m-d'))
                    ->orderBy('work_day', 'asc')->get();
        $resultAttendanceTime = [];
        $rests = [];
        foreach($works as $work){
            //複数回の休憩時間の合計を'0:00'形式で記憶
            $sumRestSeconds = 0;
            foreach($work->rests as $rest){
                if(!$work->is_demand){
                    $sumRestSeconds += $rest->finish->diffInSeconds($rest->start);
                }else{
                    $sumRestSeconds += $rest->correction_finish->diffInSeconds($rest->correction_start);
                }
            }
            $restHours = floor($sumRestSeconds / 3600);
            $restMinutes = sprintf('%02d', floor(($sumRestSeconds % 3600) / 60));
            $rests[$work->id] = "{$restHours}:{$restMinutes}";
            //休憩時間を差し引いたその日の労働時間を'0:00'形式で記憶
            if(!$work->is_demand){
                $attendanceTime = $work->getAttendanceTime();
            }else{
                $attendanceTime = $work->getAttendanceCorrectionTime();
            }
            $totalAttendanceTime = $attendanceTime - $sumRestSeconds;
            $totalAttendanceHours = floor($totalAttendanceTime / 3600);
            $totalAttendanceMinutes = sprintf('%02d', floor(($totalAttendanceTime % 3600) / 60));
            $resultAttendanceTime[$work->id] = "{$totalAttendanceHours}:{$totalAttendanceMinutes}";
        }
        return view('admin-attendance-list', compact(['year', 'month', 'day', 'works', 'rests', 'resultAttendanceTime']));
    }
}