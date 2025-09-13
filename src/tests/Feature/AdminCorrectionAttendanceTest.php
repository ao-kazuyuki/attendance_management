<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use App\Models\Demand;

class AdminCorrectionAttendanceTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID13-1:勤怠詳細画面に表示されるデータが選択したものになっている
     */
    public function testShowAdminDetailAttendance(){
        //シーダーで作成した既存一般ユーザーの勤怠情報を1件取得
        $user = User::find(2);
        $work = Work::with(['user', 'demand'])->where('user_id', '=', $user->id)->whereHas('rests')->first();
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $rest->start->format('H:i');
            $finishRests[] = $rest->finish->format('H:i');
        }
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //選択した勤怠の勤怠詳細画面を表示し、表示された情報が選択した勤怠情報と一致することを確認。
        $response = $this->get('/admin/attendance/' . $work->id)->assertStatus(200);
        $response->assertViewHas('work', $work);
    }

    /**
     *  ID13-2:出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testAttendanceTimeBeforeLeavingTime(){
        //シーダーで作成した既存一般ユーザーの勤怠情報を1件取得
        $user = User::find(2);
        $work = Work::with(['user', 'demand'])->where('user_id', '=', $user->id)->whereHas('rests')->first();
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $rest->start->format('H:i');
            $finishRests[] = $rest->finish->format('H:i');
        }
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //選択した勤怠の勤怠詳細画面を表示し、出勤時間を退勤時間より後に設定して管理者権限で修正をかける。
        $response = $this->get('/admin/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/admin/attendance/' . $work->id, [
            'start-work' => $work->finish->modify('+1 hour')->format('H:i'),
            'finish-work' => $work->finish->format('H:i'),
            'start-rest' => $startRests,
            'finish-rest' => $finishRests,
            'add-start-rest' => '',
            'add-finish-rest' => '',
            'remarks' => '入力する時間を誤った為',
        ]);
        //出勤時間に対するバリデーションエラーを確認する。
        $response->assertSessionHasErrors(['start-work' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    /**
     *  ID13-3:休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testRestStartTimeAfterLeavingTime(){
        //シーダーで作成した既存一般ユーザーの勤怠情報を1件取得
        $user = User::find(2);
        $work = Work::with(['user', 'demand'])->where('user_id', '=', $user->id)->whereHas('rests')->first();
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $work->finish->modify('+1 hour')->format('H:i');
            $finishRests[] = $rest->finish->format('H:i');
        }
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //選択した勤怠の勤怠詳細画面を表示し、休憩開始時間を退勤時間より後に設定して管理者権限で修正をかける。
        $response = $this->get('/admin/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/admin/attendance/' . $work->id, [
            'start-work' => $work->start->format('H:i'),
            'finish-work' => $work->finish->format('H:i'),
            'start-rest' => $startRests,
            'finish-rest' => $finishRests,
            'add-start-rest' => '',
            'add-finish-rest' => '',
            'remarks' => '入力する時間を誤った為',
        ]);
        //休憩時間に対するバリデーションエラーを確認する。
        for($i=0; $i<count($startRests); $i++){
            $response->assertSessionHasErrors(['start-rest.' . $i => '休憩時間が不適切な値です']);
        }
    }

    /**
     *  ID13-4:休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testRestFinishTimeAfterLeavingTime(){
        //シーダーで作成した既存一般ユーザーの勤怠情報を1件取得
        $user = User::find(2);
        $work = Work::with(['user', 'demand'])->where('user_id', '=', $user->id)->whereHas('rests')->first();
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $rest->start->format('H:i');
            $finishRests[] = $work->finish->modify('+1 hour')->format('H:i');
        }
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //選択した勤怠の勤怠詳細画面を表示し、休憩終了時間を退勤時間より後に設定して管理者権限で修正をかける。
        $response = $this->get('/admin/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/admin/attendance/' . $work->id, [
            'start-work' => $work->start->format('H:i'),
            'finish-work' => $work->finish->format('H:i'),
            'start-rest' => $startRests,
            'finish-rest' => $finishRests,
            'add-start-rest' => '',
            'add-finish-rest' => '',
            'remarks' => '入力する時間を誤った為',
        ]);
        //休憩時間に対するバリデーションエラーを確認する。
        for($i=0; $i<count($finishRests); $i++){
            $response->assertSessionHasErrors(['finish-rest.' . $i => '休憩時間が不適切な値です']);
        }
    }

    /**
     *  ID13-5:備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function testRemarksEmpty(){
        //シーダーで作成した既存一般ユーザーの勤怠情報を1件取得
        $user = User::find(2);
        $work = Work::with(['user', 'demand'])->where('user_id', '=', $user->id)->whereHas('rests')->first();
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $rest->start->format('H:i');
            $finishRests[] = $rest->finish->format('H:i');
        }
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //選択した勤怠の勤怠詳細画面を表示し、備考欄を入力せずに管理者権限で修正をかける。
        $response = $this->get('/admin/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/admin/attendance/' . $work->id, [
            'start-work' => $work->start->format('H:i'),
            'finish-work' => $work->finish->format('H:i'),
            'start-rest' => $startRests,
            'finish-rest' => $finishRests,
            'add-start-rest' => '',
            'add-finish-rest' => '',
            'remarks' => '',
        ]);
        //備考に対するバリデーションエラーを確認する。
        $response->assertSessionHasErrors(['remarks' => '備考を入力してください']);
    }
}
