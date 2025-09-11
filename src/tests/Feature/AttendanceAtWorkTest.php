<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use DateTime;

class AttendanceAtWorkTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID6-1:出勤ボタンが正しく機能する
     */
    public function testAttendanceAtWork(){
        //シーダーで作成した既存一般ユーザーでログイン（デフォルトで勤務外）
        $user = User::find(2);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //「出勤」ボタンがあるかチェック
        $response->assertSeeText('出勤');
        //「出勤」ボタンを押して勤怠ステータスを「出勤中」にする。
        $response = $this->followingRedirects()->post('/attendance/start')->assertStatus(200);
        //画面上の表示が「出勤中」に変わった事を確認
        $response->assertSeeText('出勤中');
    }

    /**
     *  ID6-2:出勤は一日一回のみできる
     */
    public function testAttendanceAtWorkOnce(){
        //シーダーで作成した既存一般ユーザーでログイン（デフォルトで勤務外）
        $user = User::find(2);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //「出勤」ボタン、「退勤」ボタンを押して退勤状態にする。
        $response = $this->followingRedirects()->post('/attendance/start')->assertStatus(200);
        $response = $this->followingRedirects()->post('/attendance/finish')->assertStatus(200);
        //退勤して同日の場合は画面に「出勤」ボタンが無いことを確認
        $response->assertDontSeeText('出勤');
    }

    /**
     *  ID6-3:出勤時刻が勤怠一覧画面で確認できる
     */
    public function testShowAttendanceTime(){
        //シーダーで作成した既存一般ユーザーでログイン（デフォルトで勤務外）
        $user = User::find(2);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //「出勤」ボタンを押す。
        $response = $this->followingRedirects()->post('/attendance/start')->assertStatus(200);
        //勤怠一覧ページに移動して、さきほどの出勤時間が画面にあるか調べる。
        $response = $this->get('/attendance/list')->assertStatus(200);
        $targetDay = new DateTime();
        $work = Work::where('user_id', '=', 2)->whereDate('work_day', '=', $targetDay)->first();
        $response->assertSeeText($work->start->format('H:i'));
    }
}
