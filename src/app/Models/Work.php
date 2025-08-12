<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start',
        'finish',
        'comment',
    ];

    protected $casts = [
        'start' => 'datetime',
        'finish' => 'datetime',
    ];

    public function rests(){
        return $this->hasMany(Rest::class);
    }

    public function getAttendanceTime(){
        return $this->finish->diffInSeconds($this->start);
    }

}
