<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use DateTime;

class GetAdminUserInfomationTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID14-1:管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function testShowStaffList(){
        //管理者以外のユーザー情報を全て取得
        $users = User::where('id', '!=', 1)->get();
        //シーダーで作成した管理者ユーザーでログイン
        $user = User::find(1);
        $response = $this->get('/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $user->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //スタッフ一覧画面を表示して、全てのスタッフ情報が存在するか調べる。
        $response = $this->get('/admin/staff/list')->assertStatus(200);
        $response->assertViewHas('users', $users);
    }

    /**
     *  ID14-2:ユーザーの勤怠情報が正しく表示される
     */
    public function testShowStaffAttendanceList(){
        //一般ユーザーを1人取得
        $user = User::find(2);
        //そのユーザーの今月の勤怠情報を取得
        $date = new DateTime();
        $startDate = (clone $date)->modify('first day of this month');
        $finishDate = (clone $date)->modify('last day of this month');
        $works = $user->getWorksBetween($startDate, $finishDate);
        //シーダーで作成した管理者ユーザーでログイン
        $AdminUser = User::find(1);
        $response = $this->get('/admin/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $AdminUser->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //スタッフ別の勤怠一覧画面を表示して、選択した一般ユーザーの月単位の勤怠情報が全てあるか調べる。
        $response = $this->get('/admin/attendance/staff/' . $user->id)->assertStatus(200);
        $response->assertViewHas('works', $works);
    }

    /**
     *  ID14-3:「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function testShowStaffPrevMonthAttendanceList(){
        //一般ユーザーを1人取得
        $user = User::find(2);
        //そのユーザーの前月の勤怠情報を取得
        $date = new DateTime();
        $prevMonthDate = (clone $date)->modify('-1 month');
        $year = $prevMonthDate->format('Y');
        $month = $prevMonthDate->format('m');
        $startDate = (clone $prevMonthDate)->modify('first day of this month');
        $finishDate = (clone $prevMonthDate)->modify('last day of this month');
        $works = $user->getWorksBetween($startDate, $finishDate);
        //シーダーで作成した管理者ユーザーでログイン
        $AdminUser = User::find(1);
        $response = $this->get('/admin/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $AdminUser->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //スタッフ別の勤怠一覧画面を表示して、選択した一般ユーザーの前月の勤怠情報が全てあるか調べる。
        $response = $this->get('/admin/attendance/staff/' . $user->id . '/' . $year . '/' . $month)->assertStatus(200);
        $response->assertViewHas('works', $works);
    }

    /**
     *  ID14-4:「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function testShowStaffNextMonthAttendanceList(){
        //一般ユーザーを1人取得
        $user = User::find(2);
        //そのユーザーの翌月の勤怠情報を取得
        $date = new DateTime();
        $nextMonthDate = (clone $date)->modify('+1 month');
        $year = $nextMonthDate->format('Y');
        $month = $nextMonthDate->format('m');
        $startDate = (clone $nextMonthDate)->modify('first day of this month');
        $finishDate = (clone $nextMonthDate)->modify('last day of this month');
        $works = $user->getWorksBetween($startDate, $finishDate);
        //シーダーで作成した管理者ユーザーでログイン
        $AdminUser = User::find(1);
        $response = $this->get('/admin/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $AdminUser->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //スタッフ別の勤怠一覧画面を表示して、選択した一般ユーザーの翌月の勤怠情報が全てあるか調べる。
        $response = $this->get('/admin/attendance/staff/' . $user->id . '/' . $year . '/' . $month)->assertStatus(200);
        $response->assertViewHas('works', $works);
    }

    /**
     *  ID14-5:「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function testShowStaffDetailAttendance(){
        //一般ユーザーを1人取得
        $user = User::find(2);
        //そのユーザーの今月の勤怠情報を取得
        $date = new DateTime();
        $startDate = (clone $date)->modify('first day of this month');
        $finishDate = (clone $date)->modify('last day of this month');
        $works = $user->getWorksBetween($startDate, $finishDate);
        //シーダーで作成した管理者ユーザーでログイン
        $AdminUser = User::find(1);
        $response = $this->get('/admin/login')->assertStatus(200);
        $response = $this->followingRedirects()->post('/admin/login', [
            'email' => $AdminUser->email,
            'password' => '12345678',
        ]);
        $this->assertAuthenticated();
        //スタッフ別の勤怠一覧画面を表示して、任意の勤怠に対応する詳細ボタンを押下し、アクセスを確認する。
        $response = $this->get('/admin/attendance/staff/' . $user->id)->assertStatus(200);
        $response = $this->get('/admin/attendance/' . $works->first()->id)->assertStatus(200);
    }
}
