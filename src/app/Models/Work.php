<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_day',
        'start',
        'finish',
        'correction_start',
        'correction_finish',
        'is_demand',
    ];

    protected $casts = [
        'work_day' => 'date',
        'start' => 'datetime',
        'finish' => 'datetime',
        'correction_start' => 'datetime',
        'correction_finish' => 'datetime',
        'is_demand' => 'boolean',
    ];

    public function demand(){
        return $this->hasOne(Demand::class);
    }

    public function rests(){
        return $this->hasMany(Rest::class);
    }

    public function getAttendanceTime(){
        return $this->finish->diffInSeconds($this->start);
    }

    public function getAttendanceCorrectionTime(){
        return $this->correction_finish->diffInSeconds($this->correction_start);
    }
}