<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CertificationTest extends TestCase
{

    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID1-1:名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function testRegisterNameHasErrors(){
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@test.jp',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /**
     *  ID1-2:メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function testRegisterEmailHasErrors(){
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => '',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     *  ID1-3:パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function testRegisterPasswordLengthErrors(){
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@test.jp',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /**
     *  ID1-4:パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function testRegisterPasswordUnMatch(){
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@test.jp',
            'password' => '12345678',
            'password_confirmation' => '123456789'
        ]);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password_confirmation' => 'パスワードと一致しません']);
    }

    /**
     *  ID1-5:パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function testRegisterPasswordHasErrors(){
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'test@test.jp',
            'password' => '',
            'password_confirmation' => '12345678'
        ]);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }


    /**
     *  ID1-6:フォームに内容が入力されていた場合、データが正常に保存される
     */
    public function testRegisterStoreMemberData(){
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response = $this->post('/register', [
            'name' => '佐藤太郎',
            'email' => 'test@test.jp',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => '佐藤太郎',
            'email' => 'test@test.jp',
        ]);
    }
}
