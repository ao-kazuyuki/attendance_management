<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use App\Models\Demand;

class GeneralCorrectionAttendanceTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID11-1:出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testAttendanceTimeBeforeLeavingTime(){
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
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $rest->start->format('H:i');
            $finishRests[] = $rest->finish->format('H:i');
        }
        //選択した勤怠の勤怠詳細画面を表示し、出勤時間を退勤時間より後に設定して修正依頼をかける。
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/attendance/' . $work->id, [
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
     *  ID11-2:休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testRestStartTimeAfterLeavingTime(){
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
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $work->finish->modify('+1 hour')->format('H:i');
            $finishRests[] = $rest->finish->format('H:i');
        }
        //選択した勤怠の勤怠詳細画面を表示し、休憩開始時間を退勤時間より後に設定して修正依頼をかける。
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/attendance/' . $work->id, [
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
     *  ID11-3:休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testRestFinishTimeAfterLeavingTime(){
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
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $rest->start->format('H:i');
            $finishRests[] = $work->finish->modify('+1 hour')->format('H:i');
        }
        //選択した勤怠の勤怠詳細画面を表示し、休憩終了時間を退勤時間より後に設定して修正依頼をかける。
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/attendance/' . $work->id, [
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
     *  ID11-4:備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function testRemarksEmpty(){
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
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $rest->start->format('H:i');
            $finishRests[] = $rest->finish->format('H:i');
        }
        //選択した勤怠の勤怠詳細画面を表示し、備考欄を入力せずに修正依頼をかける。
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/attendance/' . $work->id, [
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

    /**
     *  ID11-5:修正申請処理が実行される
     */
    public function testRequestCorrection(){
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
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $rest->start->format('H:i');
            $finishRests[] = $rest->finish->format('H:i');
        }
        //選択した勤怠の勤怠詳細画面を表示し退勤時間に誤りがあったと仮定して修正依頼をかける。
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/attendance/' . $work->id, [
            'start-work' => $work->start->format('H:i'),
            'finish-work' => $work->finish->modify('+1 hour')->format('H:i'),
            'start-rest' => $startRests,
            'finish-rest' => $finishRests,
            'add-start-rest' => '',
            'add-finish-rest' => '',
            'remarks' => '入力する時間を誤った為',
        ]);
        //申請レコードを取得
        $work->refresh();
        $demand = Demand::where('user_id', '=', $user->id)->where('work_id', '=', $work->id)->first();
        //一般ユーザーをログアウトし、管理者ユーザーでログイン
        $this->post('/logout');
        $this->assertGuest();
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //管理者用の申請一覧に申請内容があるかチェック
        $response = $this->get('/admin/stamp_correction_request/list')->assertStatus(200);
        $response->assertSeeText($demand->content);
        //管理者用の修正申請承認画面に申請内容があるかチェック
        $response = $this->get('/stamp_correction_request/approve/' . $demand->work_id)->assertStatus(200);
        $response->assertSeeText($demand->content);
    }

    /**
     *  ID11-6:「承認待ち」にログインユーザーが行った申請が全て表示されていること
     */
    public function testShowDemandWaitApproval(){
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
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $rest->start->format('H:i');
            $finishRests[] = $rest->finish->format('H:i');
        }
        //選択した勤怠の勤怠詳細画面を表示し退勤時間に誤りがあったと仮定して修正依頼をかける。
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/attendance/' . $work->id, [
            'start-work' => $work->start->format('H:i'),
            'finish-work' => $work->finish->modify('+1 hour')->format('H:i'),
            'start-rest' => $startRests,
            'finish-rest' => $finishRests,
            'add-start-rest' => '',
            'add-finish-rest' => '',
            'remarks' => '入力する時間を誤った為',
        ]);
        //申請レコードを取得
        $work->refresh();
        $demand = Demand::where('user_id', '=', $user->id)->where('work_id', '=', $work->id)->first();
        //一般ユーザーの申請一覧に申請内容があるかチェック
        $response = $this->get('/general/stamp_correction_request/list?page=wait')->assertStatus(200);
        $response->assertSeeText($demand->content);
    }

    /**
     *  ID11-7:「承認済み」に管理者が承認した修正申請が全て表示されている
     */
    public function testShowApproval(){
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
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $rest->start->format('H:i');
            $finishRests[] = $rest->finish->format('H:i');
        }
        //選択した勤怠の勤怠詳細画面を表示し退勤時間に誤りがあったと仮定して修正依頼をかける。
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/attendance/' . $work->id, [
            'start-work' => $work->start->format('H:i'),
            'finish-work' => $work->finish->modify('+1 hour')->format('H:i'),
            'start-rest' => $startRests,
            'finish-rest' => $finishRests,
            'add-start-rest' => '',
            'add-finish-rest' => '',
            'remarks' => '入力する時間を誤った為',
        ]);
        //申請レコードを取得
        $work->refresh();
        $demand = Demand::where('user_id', '=', $user->id)->where('work_id', '=', $work->id)->first();
        //一般ユーザーをログアウトし、管理者ユーザーでログイン
        $this->post('/logout');
        $this->assertGuest();
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        //管理者用の修正申請承認画面を表示して承認をかける。
        $response = $this->get('/stamp_correction_request/approve/' . $demand->work_id)->assertStatus(200);
        $response = $this->post('/admin/approval/' . $demand->work_id)->assertStatus(302);
        //承認済み一覧に先ほど承認した申請があるか調べる。
        $response = $this->get('/admin/stamp_correction_request/list?page=approval')->assertStatus(200);
        $response->assertSeeText($demand->content);
    }

    /**
     *  ID11-8:各申請の「詳細」を押下すると申請詳細画面に遷移する
     */
    public function testShowDemandDetail(){
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
        $startRests = [];
        $finishRests = [];
        foreach($work->rests as $rest){
            $startRests[] = $rest->start->format('H:i');
            $finishRests[] = $rest->finish->format('H:i');
        }
        //選択した勤怠の勤怠詳細画面を表示し退勤時間に誤りがあったと仮定して修正依頼をかける。
        $response = $this->get('/attendance/' . $work->id)->assertStatus(200);
        $response = $this->post('/attendance/' . $work->id, [
            'start-work' => $work->start->format('H:i'),
            'finish-work' => $work->finish->modify('+1 hour')->format('H:i'),
            'start-rest' => $startRests,
            'finish-rest' => $finishRests,
            'add-start-rest' => '',
            'add-finish-rest' => '',
            'remarks' => '入力する時間を誤った為',
        ]);
        //申請レコードを取得
        $work->refresh();
        $demand = Demand::where('user_id', '=', $user->id)->where('work_id', '=', $work->id)->first();
        //一般ユーザーの申請一覧を開き、先ほど申請した勤怠情報に紐づく「詳細」ボタンを押してアクセスできるか確認。
        $response = $this->get('/general/stamp_correction_request/list')->assertStatus(200);
        $response = $this->get('/attendance/' . $demand->work_id)->assertStatus(200);
    }
}
