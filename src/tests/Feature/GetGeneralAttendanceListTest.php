<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use DateTime;

class GetGeneralAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID9-1:自分が行った勤怠情報が全て表示されている
     */
    public function testShowAttendanceAll(){
        //シーダーで作成した既存一般ユーザーでログイン
        $user = User::find(2);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //勤怠一覧画面に、そのユーザーの今月の勤怠情報が全て表示されているか調べる。
        $response = $this->get('/attendance/list')->assertStatus(200);
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $startDate = (clone $date)->modify('first day of this month');
        $finishDate = (clone $date)->modify('last day of this month');
        $works = $user->getWorksBetween($startDate, $finishDate);
        $response->assertViewHas('works', $works);
    }

    /**
     *  ID9-2:勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function testShowCurrentMonth(){
        //シーダーで作成した既存一般ユーザーでログイン
        $user = User::find(2);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //勤怠一覧画面に現在の月が表示されているか確認する。
        $response = $this->get('/attendance/list')->assertStatus(200);
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $response->assertSeeText($year . '/' . $month);
    }

    /**
     *  ID9-3:「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function testShowPrevAttendanceAll(){
        //シーダーで作成した既存一般ユーザーでログイン
        $user = User::find(2);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //今月を確認
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        //その前月の日付を生成・指定して勤怠一覧を取得（「前月」ボタンを押下）
        $selectDate = new DateTime($year . '-' . $month . '-1');
        $prevDate = (clone $selectDate)->modify('first day of previous month');
        $prevYear = $prevDate->format('Y');
        $prevMonth = $prevDate->format('m');
        $response = $this->get('/attendance/list/' . $prevYear . '/' . $prevMonth)->assertStatus(200);
        //前月の全ての勤怠情報が表示されているか調べる
        $startDate = (clone $prevDate)->modify('first day of this month');
        $finishDate = (clone $prevDate)->modify('last day of this month');
        $works = $user->getWorksBetween($startDate, $finishDate);
        $response->assertViewHas('works', $works);
    }

    /**
     *  ID9-4:「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function testShowNextAttendanceAll(){
        //シーダーで作成した既存一般ユーザーでログイン
        $user = User::find(2);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //今月を確認
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        //その翌月の日付を生成・指定して勤怠一覧を取得（「翌月」ボタンを押下）
        $selectDate = new DateTime($year . '-' . $month . '-1');
        $nextDate = (clone $selectDate)->modify('first day of next month');
        $nextYear = $nextDate->format('Y');
        $nextMonth = $nextDate->format('m');
        $response = $this->get('/attendance/list/' . $nextYear . '/' . $nextMonth)->assertStatus(200);
        //翌月の全ての勤怠情報が表示されているか調べる
        $startDate = (clone $nextDate)->modify('first day of this month');
        $finishDate = (clone $nextDate)->modify('last day of this month');
        $works = $user->getWorksBetween($startDate, $finishDate);
        $response->assertViewHas('works', $works);
    }

    /**
     *  ID9-5:「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function testShowDetail(){
        //シーダーで作成した既存一般ユーザーでログイン
        $user = User::find(2);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //そのユーザーの勤怠情報を1件取得
        $work = Work::where('user_id', '=', $user->id)->first();
        //その勤怠レコードに対応する詳細ボタンを押下しアクセス成功を確認
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
    }

}
