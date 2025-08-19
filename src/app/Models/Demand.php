<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demand extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_id',
        'content',
        'request_day',
        'status',
    ];

    protected $casts = [
        'request_day' => 'date',
    ];
}