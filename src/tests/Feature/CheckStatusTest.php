<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class CheckStatusTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID5-1:勤務外の場合、勤怠ステータスが正しく表示される
     */
    public function testOffDutyAttendance(){
        //シーダーで作成した既存一般ユーザーでログイン（デフォルトで勤務外）
        $user = User::find(2);
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        //勤務外の勤怠ステータス表示があるかチェック
        $this->assertAuthenticated();
        $response->assertSeeText('勤務外');
    }

    /**
     *  ID5-2:出勤中の場合、勤怠ステータスが正しく表示される
     */
    public function testAtWorkAttendance(){
        //シーダーで作成した既存一般ユーザーでログインし、勤怠ステータスを「出勤中」にしておく
        $user = User::find(2);
        $user->updateStatusByUserId('2');       //出勤中へ
        $user->setStartAttendance();            //勤怠レコードを生成
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        //出勤中の勤怠ステータス表示があるかチェック
        $this->assertAuthenticated();
        $response->assertSeeText('出勤中');
    }

    /**
     *  ID5-3:休憩中の場合、勤怠ステータスが正しく表示される
     */
    public function testAtRestAttendance(){
        //シーダーで作成した既存一般ユーザーでログインし、勤怠ステータスを「休憩中」にしておく
        $user = User::find(2);
        $user->updateStatusByUserId('2');       //出勤中へ
        $user->setStartAttendance();            //勤怠レコードを生成
        $user->updateStatusByUserId('3');       //休憩中へ
        $user->setStartRest();                  //勤怠レコードに紐づく休憩レコードを生成
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        //休憩中の勤怠ステータス表示があるかチェック
        $this->assertAuthenticated();
        $response->assertSeeText('休憩中');
    }

    /**
     *  ID5-4:退勤済の場合、勤怠ステータスが正しく表示される
     */
    public function testLeavingWorkAttendance(){
        //シーダーで作成した既存一般ユーザーでログインし、勤怠ステータスを「退勤済」にしておく
        $user = User::find(2);
        $user->updateStatusByUserId('2');       //出勤中へ
        $user->setStartAttendance();            //勤怠レコードを生成
        $user->updateStatusByUserId('4');       //退勤済へ
        $user->setFinishAttendance();           //退勤処理
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        //退勤済の勤怠ステータス表示があるかチェック
        $this->assertAuthenticated();
        $response->assertSeeText('退勤済');
    }

}
