<?php

namespace Database\Seeders;

use DateTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => '山田太郎',
            'email' => 'admin@test.jp',
            'email_verified_at' => new DateTime(),
            'password' => Hash::make('12345678'),
            'status_id' => '1',
            'is_admin' => true,
        ]);
    }
}