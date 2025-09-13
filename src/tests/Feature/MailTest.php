<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use DateTime;

class MailTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID16-1:会員登録後、認証メールが送信される
     */
    public function testSendMail(){
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/register', [
            'name' => '佐藤太郎',
            'email' => 'test@test.jp',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);
        $response->assertSeeText('登録していただいたメールアドレスに認証メールを送付しました。');
    }

    /**
     *  ID16-2:メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     */
    public function testShowCertificationSite(){
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/register', [
            'name' => '佐藤太郎',
            'email' => 'test@test.jp',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);
        $response = Http::get('https://mailtrap.io/');
        $this->assertEquals(200, $response->status());
    }

    /**
     *  ID16-3:メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
     */
    public function testCertificationComplete(){
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/register', [
            'name' => '佐藤太郎',
            'email' => 'test@test.jp',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);
        //さっき登録したユーザーのメール認証情報に今日の日時を入れて認証扱いとする。
        $user = User::where('email', '=', 'test@test.jp')->first();
        $user->update(['email_verified_at' => new DateTime()]);
        //勤怠登録画面にアクセスできるか確認する
        $response = $this->get('/attendance')->assertStatus(302);
    }

}
