<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_id',
        'rest_day',
        'start',
        'finish',
        'correction_start',
        'correction_finish',
    ];

    protected $casts = [
        'rest_day' => 'date',
        'start' => 'datetime',
        'finish' => 'datetime',
        'correction_start' => 'datetime',
        'correction_finish' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function work(){
        return $this->belongsTo(Work::class);
    }
}
