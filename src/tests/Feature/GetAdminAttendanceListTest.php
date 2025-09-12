<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use DateTime;

class GetAdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID12-1:その日になされた全ユーザーの勤怠情報が正確に確認できる
     */
    public function testAttendanceTimeBeforeLeavingTime(){
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //その日の勤怠情報が画面にあるか調べる。
        $today = new DateTime();
        $works = Work::whereDate('work_day', '=', $today->format('Y-m-d'))->get();
        $response->assertViewHas('works', $works);
    }

    /**
     *  ID12-2:遷移した際に現在の日付が表示される
     */
    public function testShowCurrentDate(){
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //現在の日付情報が画面にあるか確認する。
        $today = new DateTime();
        $works = Work::whereDate('work_day', '=', $today->format('Y-m-d'))->get();
        $response->assertSeeText($today->format('Y年n月d日の勤怠'));
    }

    /**
     *  ID12-3:「前日」を押下した時に前の日の勤怠情報が表示される
     */
    public function testShowPrevDate(){
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //今日を確認
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');
        //その前日の日付を生成・指定して勤怠一覧を取得（「前日」ボタンを押下）
        $selectDate = new DateTime($year . '-' . $month . '-' . $day);
        $prevDate = (clone $selectDate)->modify('-1 day');
        $prevYear = $prevDate->format('Y');
        $prevMonth = $prevDate->format('m');
        $prevDay = $prevDate->format('d');
        $response = $this->get('/admin/attendance/list/' . $prevYear . '/' . $prevMonth . '/' . $prevDay)->assertStatus(200);
        //前日の勤怠情報が画面にあるか調べる。
        $works = Work::with(['rests', 'user', 'demand'])->whereDate('work_day', '=', $prevDate->format('Y-m-d'))->get();
        $response->assertViewHas('works', $works);
    }

    /**
     *  ID12-4:「翌日」を押下した時に次の日の勤怠情報が表示される
     */
    public function testShowNextDate(){
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //今日を確認
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');
        //その翌日の日付を生成・指定して勤怠一覧を取得（「翌日」ボタンを押下）
        $selectDate = new DateTime($year . '-' . $month . '-' . $day);
        $nextDate = (clone $selectDate)->modify('+1 day');
        $nextYear = $nextDate->format('Y');
        $nextMonth = $nextDate->format('m');
        $nextDay = $nextDate->format('d');
        $response = $this->get('/admin/attendance/list/' . $nextYear . '/' . $nextMonth . '/' . $nextDay)->assertStatus(200);
        //翌日の勤怠情報が画面にあるか調べる。
        $works = Work::with(['rests', 'user', 'demand'])->whereDate('work_day', '=', $nextDate->format('Y-m-d'))->get();
        $response->assertViewHas('works', $works);
    }
}
