<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = array('勤務外', '出勤中', '休憩中', '退勤済');
        foreach($statuses as $status){
            self::setSeederData($status);
        }
    }

    public static function setSeederData($content){
        DB::table('statuses')->insert(['content' => $content]);
    }
}
