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
            'name' => 'killer',
            'email' => '1054979645@qq.com',
            'password' => app('hash')->make('johndoe'),
            'remember_token' => str_random(10),
            'status'=>'admin'
        ]);
    }
}
