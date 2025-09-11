<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use DateTime;

class LeavingWorkTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID8-1:退勤ボタンが正しく機能する
     */
    public function testLeavingWork(){
        //シーダーで作成した既存一般ユーザーでログイン（デフォルトで勤務外）
        $user = User::find(2);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //「出勤」ボタンを押して勤怠ステータスを「出勤中」にする。
        $response = $this->followingRedirects()->post('/attendance/start')->assertStatus(200);
        //「退勤」ボタンがあるかチェック
        $response->assertSeeText('退勤');
        //退勤の処理を行い、勤怠ステータスが「退勤済」であることを確認する。
        $response = $this->followingRedirects()->post('/attendance/finish')->assertStatus(200);
        $response->assertSeeText('退勤済');
    }

    /**
     *  ID8-2:退勤時刻が勤怠一覧画面で確認できる
     */
    public function testShowLeavingWorkTime(){
        //シーダーで作成した既存一般ユーザーでログイン（デフォルトで勤務外）
        $user = User::find(2);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //「出勤」と「退勤」の処理を行う。
        $response = $this->followingRedirects()->post('/attendance/start')->assertStatus(200);
        $response = $this->followingRedirects()->post('/attendance/finish')->assertStatus(200);
        //勤怠一覧ページに移動して、さきほどの退勤時間が画面にあるか調べる。
        $response = $this->get('/attendance/list')->assertStatus(200);
        $targetDay = new DateTime();
        $work = Work::where('user_id', '=', 2)->whereDate('work_day', '=', $targetDay)->first();
        $response->assertSeeText($work->finish->format('H:i'));
    }
}
