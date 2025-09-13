<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use App\Models\Demand;

class AdminApprovalTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID15-1:承認待ちの修正申請が全て表示されている
     */
    public function testShowRequestCorrectionAtWait(){
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
        //修正依頼をかける
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
        $AdminUser = User::find(1);
        $response = $this->get('/admin/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $AdminUser->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //管理者用の申請一覧の承認待ちに申請内容があるかチェック
        $response = $this->get('/admin/stamp_correction_request/list?page=wait')->assertStatus(200);
        $response->assertSeeText($demand->content);
    }

    /**
     *  ID15-2:承認済みの修正申請が全て表示されている
     */
    public function testShowRequestCorrectionAtApproval(){
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
        //修正依頼をかける
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
        //一般ユーザーをログアウトし、管理者ユーザーでログイン
        $this->post('/logout');
        $this->assertGuest();
        //シーダーで作成した管理者ユーザーでログイン
        $AdminUser = User::find(1);
        $response = $this->get('/admin/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $AdminUser->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //修正依頼を承認する
        $response = $this->post('/admin/approval/' . $work->id)->assertStatus(302);
        //申請レコードを取得
        $work->refresh();
        $demand = Demand::where('user_id', '=', $user->id)->where('work_id', '=', $work->id)->first();
        //管理者用の申請一覧の承認済みに申請内容があるかチェック
        $response = $this->get('/admin/stamp_correction_request/list?page=approval')->assertStatus(200);
        $response->assertSeeText($demand->content);
    }

    /**
     *  ID15-3:修正申請の詳細内容が正しく表示されている
     */
    public function testShowRequestCorrectionDetail(){
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
        //修正依頼をかける
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
        $AdminUser = User::find(1);
        $response = $this->get('/admin/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $AdminUser->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //修正申請承認画面にさきほどの申請内容が表示されているか調べる
        $response = $this->get('/stamp_correction_request/approve/' . $work->id)->assertStatus(200);
        $response->assertSeeText($demand->content);
    }

    /**
     *  ID15-4:修正申請の承認処理が正しく行われる
     */
    public function testApproval(){
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
        //修正依頼をかける
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
        //一般ユーザーをログアウトし、管理者ユーザーでログイン
        $this->post('/logout');
        $this->assertGuest();
        //シーダーで作成した管理者ユーザーでログイン
        $AdminUser = User::find(1);
        $response = $this->get('/admin/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $AdminUser->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //申請レコードを取得
        $work->refresh();
        $demand = Demand::where('user_id', '=', $user->id)->where('work_id', '=', $work->id)->first();
        //修正依頼を承認する
        $response = $this->post('/admin/approval/' . $work->id)->assertStatus(302);
        //修正申請承認画面に「承認済み」のラベルがあるか調べる
        $response = $this->get('/stamp_correction_request/approve/' . $work->id)->assertStatus(200);
        $response->assertSeeText("承認済み");
    }
}
