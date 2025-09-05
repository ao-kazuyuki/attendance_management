<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GeneralLoginTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID2-1:メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */ 
    public function testLoginMailHasErrors(){
        //テスト用ユーザーの生成
        $user = User::create([
            'name' => '佐藤太郎',
            'email' => 'test@test.jp',
            'password' => Hash::make('12345678'),
            'status_id' => '1',
        ]);
        //生成したユーザーでテスト
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response = $this->post('/login', [
            'email' => '',
            'password' => '12345678',
        ]);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     *  ID2-2:パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function testLoginPasswordHasErrors(){
        //テスト用ユーザーの生成
        $user = User::create([
            'name' => '佐藤太郎',
            'email' => 'test@test.jp',
            'password' => Hash::make('12345678'),
            'status_id' => '1',
        ]);
        //生成したユーザーでテスト
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response = $this->post('/login', [
            'email' => 'test@test.jp',
            'password' => '',
        ]);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    } 

    /**
     *  ID2-3:登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function testLoginErrors(){
        //テスト用ユーザーの生成
        $user = User::create([
            'name' => '佐藤太郎',
            'email' => 'test@test.jp',
            'password' => Hash::make('12345678'),
            'status_id' => '1',
        ]);
        //生成したユーザーでテスト
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response = $this->post('/login', [
            'email' => 'test2@test.jp',
            'password' => '12345678',
        ]);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }

}
