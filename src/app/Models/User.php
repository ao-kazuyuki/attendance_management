<?php

namespace App\Models;

use DateTime;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function status(){
        return $this->belongsTo(Status::class);
    }

    public function works(){
        return $this->hasMany(Work::class);
    }

    public function rests(){
        return $this->hasMany(Rest::class);
    }

    /**
     *  ユーザーの現在の勤務状況を返します。
     *  @return string '勤務外','出勤中','休憩中','退勤済'のいずれかを返します。
     */
    public function getStatus(){
        return optional($this->status)->content;
    }

    /**
     *  ユーザーの勤務状況ステータスを更新します。
     *  @param string $statusId 勤務ステータスidを指定します。
     *  @return void
     */
    public function updateStatusByUserId($statusId){
        return $this->update(['status_id' => $statusId]);
    }

    public function isWorkToday():bool{
        $now = new DateTime();
        return $this->works()->whereDate('created_at', $now->format('Y-m-d'))->exists();
    }

    /**
     *  出勤処理を行います。
     *  ユーザーが「出勤」ボタンをクリックしたときの時間を'works'テーブルの'start'カラムに記録します。
     *  @return void
     */
    public function setStartAttendance(){
        $now = new DateTime();
        $isWork = $this->works()->whereDate('created_at', $now->format('Y-m-d'))->exists();
        if(!$isWork){
            $this->works()->create(['start' => $now]);
        }
    }

    /**
     *  退勤処理を行います。
     *  ユーザーが「退勤」ボタンをクリックしたときの時間を'works'テーブルの'finish'カラムに記録します。
     *  @return void
     */
    public function setFinishAttendance(){
        $now = new DateTime();
        $targetWork = $this->works()->whereDate('created_at', $now->format('Y-m-d'))->first();
        if(!is_null($targetWork->start)){
            $targetWork->update(['finish' => $now]);
        }
    }

    /**
     *  休憩(入)処理を行います。
     *  ユーザーが「休憩入」ボタンをクリックしたときの時間を'rests'テーブルの'start'カラムに記録します。
     *  @return void
     */
    public function setStartRest(){
        $now = new DateTime();
        $targetWork = $this->works()->whereDate('created_at', $now->format('Y-m-d'))->first();
        $this->rests()->create([
            'work_id' => $targetWork->id,
            'start' => $now,
        ]);
    }

    /**
     *  休憩(戻)処理を行います。
     *  ユーザーが「休憩戻」ボタンをクリックしたときの時間を'rests'テーブルの'finish'カラムに記録します。
     *  @return void
     */
    public function setFinishRest(){
        $now = new DateTime();
        $targetRest = $this->rests()->whereDate('created_at', $now->format('Y-m-d'))->orderBy('created_at', 'desc')->first();
        if(!is_null($targetRest->start)){
            $targetRest->update(['finish' => $now]);
        }
    }

    /**
     *  指定した日付間の勤務記録を取得します。
     *  @param DateTime $startDate 開始日
     *  @param DateTime $finishDate 終了日
     *  @return \Illuminate\Database\Eloquent\Collection|\App\Models\Work[]
     */
    public function getWorksBetween(DateTime $startDate, DateTime $finishDate){
        return $this->works()
                    ->with('rests')
                    ->whereDate('created_at', '>=', $startDate->format('Y-m-d'))
                    ->whereDate('created_at', '<=', $finishDate->format('Y-m-d'))
                    ->get();
    }

}
