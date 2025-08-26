<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(StatusesTableSeeder::class);        //勤怠状況ステータス(勤務外、出勤中、休憩中、退勤済)を生成
        $this->call(UsersTableSeeder::class);           //管理者ユーザーを生成
        $baseStartHour = 8;                             //基準の出勤時間
        $createUserCount = 10;                          //一般ユーザーの生成数
        $targetInterval = Carbon::today()->subDays(30);     //直近30日間が勤怠データの生成期間

        //一般ユーザーの生成と勤怠データの作成
        for($i=0; $i<$createUserCount; $i++){
            $user = User::factory()->create();
            //本日から数えて30日分の出勤データを生成
            for($j=0; $j<30; $j++){
                //出勤するかどうか
                if(rand(0, 1) == 1){
                    $targetDate = $targetInterval->copy()->addDays($j)->startOfDay();
                    $workStart = $targetDate->copy()->addHours($baseStartHour);
                    //8時を基準として±3時間の範囲で出勤時間をランダム生成
                    if(rand(0, 1) == 1){
                        $workStart->addHours(rand(0, 2));
                        $workStart->addMinutes(rand(0, 59));
                    }else{
                        $workStart->subHours(rand(0, 2));
                        $workStart->subMinutes(rand(0, 59));
                    }
                    $workFinish = $workStart->copy();
                    $workFinish->addHours(rand(1, 9));
                    $workFinish->addMinutes(rand(0, 59));
                    $work = Work::create([
                        'user_id' => $user->id,
                        'work_day' => $targetDate->format('Y-m-d'),
                        'start' => $workStart,
                        'finish' => $workFinish,
                    ]);
                    
                    //その日の勤務時間を調べ休憩を与える
                    $attendanceHour = $workFinish->diffInHours($workStart);

                    //勤務時間が8時間以上の場合、30分の休憩を2回与える
                    if($attendanceHour >= 8){
                        $restStart = $workStart->copy()->addHours(3);
                        $restFinish = $restStart->copy()->addMinutes(30);
                        Rest::create([
                            'user_id' => $user->id,
                            'work_id' => $work->id,
                            'rest_day' => $work->work_day,
                            'start' => $restStart,
                            'finish' => $restFinish,
                        ]);
                        $restStart = $workStart->copy()->addHours(6);
                        $restFinish = $restStart->copy()->addMinutes(30);
                        Rest::create([
                            'user_id' => $user->id,
                            'work_id' => $work->id,
                            'rest_day' => $work->work_day,
                            'start' => $restStart,
                            'finish' => $restFinish,
                        ]);
                    }else if($attendanceHour >= 5){
                        $restStart = $workStart->copy()->addHours(2);
                        $restFinish = $restStart->copy()->addMinutes(30);
                        Rest::create([
                            'user_id' => $user->id,
                            'work_id' => $work->id,
                            'rest_day' => $work->work_day,
                            'start' => $restStart,
                            'finish' => $restFinish,
                        ]);
                    }


                }
            }
        }
    }
}
