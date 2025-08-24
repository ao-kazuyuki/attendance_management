<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\CorrectionRequest;
use App\Models\Demand;
use App\Models\Rest;
use App\Models\User;
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
        $email = $request->input('email');
        $pw = $request->input('password');
        if(Auth::validate(['email' => $email, 'password' => $pw])){
            $user = User::where('email', '=', $email)->first();
            if(!$user->is_admin){
                return redirect()->route('login');
            }
            $admin_user = [
                'email' => $request->input('email'),
                'password' => $request->input('password'),
            ];
            if(Auth::attempt($admin_user, $request->filled('remember'))) {
                $request->session()->regenerate();
                return redirect()->route('admin-show-attendance-list');
            }
        }else{
            throw ValidationException::withMessages([
                'email' => __('ログイン情報が登録されていません'),
            ]);
        }
    }

    public function logout( Request $request ){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
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
        $works = Work::with('rests', 'user', 'demand')
                    ->where('work_day', '=', $date->format('Y-m-d'))
                    ->orderBy('work_day', 'asc')->get();
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
        return view('admin-attendance-list', compact(['year', 'month', 'day', 'works', 'rests', 'resultAttendanceTime']));
    }

    public function showDetailAttendance($id){
        $work = Work::with(['user', 'demand'])->find($id);
        $user = $work->user;
        $rests = Rest::where('user_id', $user->id)->where('work_id', $work->id)->get();
        $restCount = $rests->count();
        if(!$work->is_demand){
            return view('admin-attendance-detail', compact(['user', 'work', 'rests', 'restCount']));
        }else{
            $demand = $work->demand;
            return view('admin-attendance-detail', compact(['user', 'work', 'rests', 'restCount', 'demand']));
        }
    }

    public function storeCorrection($id, CorrectionRequest $request){
        $work = Work::with(['rests', 'user'])->find($id);
        $user = $work->user;
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
            'status' => '承認済み',
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
        return redirect(route('admin-show-detail-attendance', ['id' => $id]));        
    }

    public function showStaffList(){
        $users = User::where('is_admin', '!=', true)->get();
        return(view('admin-staff-list', compact(['users'])));
    }

    /**
     *  スタッフ別に今月の勤怠一覧画面を出力します。
     */
    public function showStaffAttendanceList($id){
        $date = new DateTime();
        return $this->showStaffAttendanceListMain($id, $date);
    }

    /**
     *  スタッフ別に指定した年月で勤怠一覧画面を出力します。
     */
    public function selectStaffAttendanceList($id, $year, $month){
        $date = new DateTime($year . '-' . $month . '-1');
        return $this->showStaffAttendanceListMain($id, $date);
    }

    /**
     *  スタッフ別に今月または指定した年月で勤怠一覧画面を出力します。
     */
    public function showStaffAttendanceListMain($id, DateTime $date){
        //出力年月の初日から月末までの勤怠レコードを取得
        $user = User::find($id);
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
        return view('admin-staff-attendance-list', compact(['user', 'year', 'month', 'startDate', 'finishDate', 'works', 'rests', 'resultAttendanceTime']));
    }

    public function showCorrectionList(Request $request){
        if($request->page == 'wait' || $request->page == ''){
            $demands = Demand::with(['user', 'work'])->where('status', '承認待ち')->get();
        }else if($request->page == 'approval'){
            $demands = Demand::with(['user', 'work'])->where('status', '承認済み')->get();
        }
        return view('admin-attendance-correction', compact('request', 'demands'));
    }

}