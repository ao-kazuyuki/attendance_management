<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;

class GetGeneralAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID10-1:勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function testShowDetailUserName(){
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
        //その勤怠レコードに対応する勤怠詳細画面を表示する
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        //詳細画面に表示された名前がログインしたユーザー名になっているか確認する
        $response->assertSeeText($user->name);
    }

    /**
     *  ID10-2:勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function testShowDetailDate(){
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
        //選択した勤怠レコードの日付情報が勤怠詳細画面に表示されているか確認する
        $year = $work->work_day->format('Y');
        $month = $work->work_day->format('n');
        $day = $work->work_day->format('d');
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        $response->assertSeeText($year . '年');
        $response->assertSeeText($month . '月' . $day . '日');
    }

    /**
     *  ID10-3:「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function testShowDetailAttendanceTime(){
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
        //選択した勤怠レコードの出勤・退勤時間が画面に表示されているか調べる
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        $response->assertSee($work->start->format('H:i'), false);
        $response->assertSee($work->finish->format('H:i'), false);
    }

    /**
     *  ID10-4:「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function testShowDetailAttendanceRestTime(){
        //シーダーで作成した既存一般ユーザーでログイン
        $user = User::find(2);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //そのユーザーの勤怠情報を1件取得
        $work = Work::where('user_id', '=', $user->id)->whereHas('rests')->first();
        //選択した勤怠レコードに対応する休憩レコードの休憩入時間・休憩戻時間が画面に表示されているか調べる。
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        foreach($work->rests as $rest){
            $response->assertSee($rest->start->format('H:i'), false);
            $response->assertSee($rest->finish->format('H:i'), false);
        }
    }
}
