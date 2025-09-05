<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID3-1:メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */ 
    public function testLoginMailHasErrors(){
        //テスト用の管理者ユーザーの生成
        $user = User::create([
            'name' => '伊藤太郎',
            'email' => 'admin2@test.jp',
            'password' => Hash::make('12345678'),
            'status_id' => '1',
            'is_admin' => true,
        ]);
        //生成した管理者ユーザーでテスト
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => '12345678',
        ]);
        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     *  ID3-2:パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function testLoginPasswordHasErrors(){
        //テスト用の管理者ユーザーの生成
        $user = User::create([
            'name' => '伊藤太郎',
            'email' => 'admin2@test.jp',
            'password' => Hash::make('12345678'),
            'status_id' => '1',
            'is_admin' => true,
        ]);
        //生成した管理者ユーザーでテスト
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response = $this->post('/admin/login', [
            'email' => 'admin2@test.jp',
            'password' => '',
        ]);
        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    } 

    /**
     *  ID3-3:登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function testLoginErrors(){
        //テスト用の管理者ユーザーの生成
        $user = User::create([
            'name' => '伊藤太郎',
            'email' => 'admin2@test.jp',
            'password' => Hash::make('12345678'),
            'status_id' => '1',
            'is_admin' => true,
        ]);
        //生成した管理者ユーザーでテスト
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response = $this->post('/admin/login', [
            'email' => 'test3@test.jp',
            'password' => '12345678',
        ]);
        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
