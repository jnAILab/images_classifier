<?php

use Illuminate\Database\Seeder;
class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
        	'user_id'=>uniqid(),
            'name' => 'killer02',
            'email' => '1054979645@qq.com',
            'password' => app('hash')->make('killer'),
            'remember_token' => str_random(10),
            'status'=>'admin'
        ]);
        DB::table('users')->insert([
            'user_id'=>uniqid(),
            'name' => 'killer02',
            'email' => '1054979646@qq.com',
            'password' => app('hash')->make('killer'),
            'remember_token' => str_random(10),
            'status'=>'admin'
        ]);
        DB::table('users')->insert([
            'user_id'=>uniqid(),
            'name' => 'yuke01',
            'email' => '1403561447@qq.com',
            'password' => app('hash')->make('killer'),
            'remember_token' => str_random(10),
            'status'=>'client'
        ]);
        DB::table('users')->insert([
            'user_id'=>uniqid(),
            'name' => 'yuke02',
            'email' => '1403561448@qq.com',
            'password' => app('hash')->make('killer'),
            'remember_token' => str_random(10),
            'status'=>'client'
        ]);
        DB::table('users')->insert([
            'user_id'=>uniqid(),
            'name' => 'yuke03',
            'email' => '1403561449@qq.com',
            'password' => app('hash')->make('killer'),
            'remember_token' => str_random(10),
            'status'=>'client'
        ]);
    }
}
