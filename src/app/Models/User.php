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
     *  ユーザーの現在の勤怠状況を返します。
     *  @return string '勤務外','出勤中','休憩中','退勤済'のいずれかを返します。
     */
    public function getStatus(){
        return optional($this->status)->content;
    }

    /**
     *  ユーザーの勤怠状況を更新します。
     *  @param string $statusId 勤務ステータスidを指定します。
     *  @return void
     */
    public function updateStatusByUserId($statusId){
        return $this->update(['status_id' => $statusId]);
    }

    /**
     *  指定した日付に対応する勤怠レコードが存在するか調べます。
     *  @param DateTime $date 対象の日付。引数を指定しない場合は現在の日付が指定されます。
     *  @return bool 該当の勤怠レコードが存在する場合はtrue,そうでない場合はfalseを返します。
     */
    public function isWork(DateTime $date = new Datetime()):bool{
        return $this->works()->whereDate('work_day', $date->format('Y-m-d'))->exists();
    }

    /**
     *  指定した日付に対応する勤怠レコードを取得します。
     *  @param DateTime $date 対象の日付。引数を指定しない場合は現在の日付が指定されます。
     *  @return \Illuminate\Database\Eloquent\Collection|\App\Models\Work[]
     */
    public function getWork(DateTime $date = new Datetime()){
        return $this->works()->whereDate('work_day', $date->format('Y-m-d'))->first();
    }

    /**
     *  指定した日付に対応する最新の休憩レコードを取得します。
     *  @param DateTime $date 対象の日付。引数を指定しない場合は現在の日付が指定されます。
     *  @return \Illuminate\Database\Eloquent\Collection|\App\Models\Rest[]
     */
    public function getLatestRest(DateTime $date = new Datetime()){
        return $this->rests()->whereDate('rest_day', $date->format('Y-m-d'))->orderBy('start', 'desc')->first();
    }

    /**
     *  出勤処理を行います。
     *  ユーザーが「出勤」ボタンをクリックしたときの日付と時間を'works'テーブルのレコードに記録します。
     *  @return void
     */
    public function setStartAttendance(){
        $now = new DateTime();
        if(!$this->isWork()){
            $this->works()->create(['work_day' => $now, 'start' => $now]);
        }
    }

    /**
     *  退勤処理を行います。
     *  ユーザーが「退勤」ボタンをクリックしたときの時間を'works'テーブルのレコードに記録します。
     *  @return void
     */
    public function setFinishAttendance(){
        $now = new DateTime();
        $work = $this->getWork($now);
        if(!is_null($work->start)){
            $work->update(['finish' => $now]);
        }
    }

    /**
     *  休憩(入)処理を行います。
     *  ユーザーが「休憩入」ボタンをクリックしたときの時間を'rests'テーブルのレコードに記録します。
     *  @return void
     */
    public function setStartRest(){
        $now = new DateTime();
        $work = $this->getWork($now);
        $this->rests()->create([
            'work_id' => $work->id,
            'rest_day' => $now,
            'start' => $now,
        ]);
    }

    /**
     *  休憩(戻)処理を行います。
     *  ユーザーが「休憩戻」ボタンをクリックしたときの時間を'rests'テーブルのレコードに記録します。
     *  @return void
     */
    public function setFinishRest(){
        $now = new DateTime();
        $rest = $this->getLatestRest($now);
        if(!is_null($rest->start)){
            $rest->update(['finish' => $now]);
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
                    ->with('rests', 'demand')
                    ->whereDate('work_day', '>=', $startDate->format('Y-m-d'))
                    ->whereDate('work_day', '<=', $finishDate->format('Y-m-d'))
                    ->get();
    }

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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'status_id',
        'is_admin',
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
        'is_admin' => 'boolean',
    ];
}
