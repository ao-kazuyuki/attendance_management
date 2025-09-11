<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;
use DateTime;

class RestTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID7-1:休憩ボタンが正しく機能する
     */
    public function testRest(){
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
        //「休憩入」ボタンがあるかチェック
        $response->assertSeeText('休憩入');
        //「休憩入」ボタンを押して勤怠ステータスを「休憩中」にする。
        $response = $this->followingRedirects()->post('/attendance/rest-in')->assertStatus(200);
        //画面上の表示が「休憩中」に変わった事を確認
        $response->assertSeeText('休憩中');
    }

    /**
     *  ID7-2:休憩は一日に何回でもできる
     */
    public function testAnyRest(){
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
        //「休憩入」ボタンをと「休憩戻」ボタンを押す
        $response = $this->followingRedirects()->post('/attendance/rest-in')->assertStatus(200);
        $response = $this->followingRedirects()->post('/attendance/rest-out')->assertStatus(200);
        //1回休憩を終えたあとでもまた「休憩入」ボタンがあることを確認
        $response->assertSeeText('休憩入');
    }

    /**
     *  ID7-3:休憩戻ボタンが正しく機能する
     */
    public function testReturnRest(){
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
        //「休憩入」ボタンを押す
        $response = $this->followingRedirects()->post('/attendance/rest-in')->assertStatus(200);
        //「休憩戻」ボタンがあるか確認する
        $response->assertSeeText('休憩戻');
        //休憩を終えたあと勤怠ステータスが「出勤中」になっている事を確認
        $response = $this->followingRedirects()->post('/attendance/rest-out')->assertStatus(200);
        $response->assertSeeText('出勤中');
    }

    /**
     *  ID7-4:休憩戻は一日に何回でもできる
     */
    public function testAnyReturnRest(){
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
        //「休憩入」と「休憩戻」の処理後、再度「休憩入」のボタンを押す。
        $response = $this->followingRedirects()->post('/attendance/rest-in')->assertStatus(200);
        $response = $this->followingRedirects()->post('/attendance/rest-out')->assertStatus(200);
        $response = $this->followingRedirects()->post('/attendance/rest-in')->assertStatus(200);
        //2回目以降の休憩でも「休憩戻」のボタンがあることを確認する
        $response->assertSeeText('休憩戻');
    }

    /**
     *  ID7-5:休憩時刻が勤怠一覧画面で確認できる
     */
    public function testShowRestTime(){
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
        //「休憩入」と「休憩戻」の処理を行う。
        $response = $this->followingRedirects()->post('/attendance/rest-in')->assertStatus(200);
        $response = $this->followingRedirects()->post('/attendance/rest-out')->assertStatus(200);
        //勤怠一覧ページに移動して、さきほどの出勤の休憩情報が画面にあるか調べる。
        $response = $this->get('/attendance/list')->assertStatus(200);
        $targetDay = new DateTime();
        $work = Work::with('rests')->where('user_id', '=', 2)->whereDate('work_day', '=', $targetDay)->first();
        foreach($work->rests as $rest){
            $response->assertSeeText($rest->start->format('H:i'));
            $response->assertSeeText($rest->finish->format('H:i'));
        }
    }
}