<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class GetDateTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     *  ID4-1:現在の日時情報がUIと同じ形式で出力されている
     */
    public function testCheckDateAndTime(){
        //シーダーで作成した既存ユーザーでログイン
        $user = User::find(2);
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response = $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => '87654321',
        ]);
        $this->assertAuthenticated();
        //サーバーから送った日付情報「xxxx年xx月xx日(x)」が画面にあるか
        $viewData = $response->getOriginalContent()->getData();
        $outputDate = $viewData['outputDate'];
        $response->assertSeeText($outputDate);
        //サーバーから送った時刻情報(xx:xx)が画面にあるか
        $outputTime = $viewData['outputTime'];
        $response->assertSeeText($outputTime);
    }
}