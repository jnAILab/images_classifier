<?php

use Illuminate\Database\Seeder;
class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = DB::table('users')->where('status','admin')->get();
        foreach($clients as $index => $client){
            DB::table('admins')->insert([
                'user_id'=>$client->user_id,
                'realname' => '陈慧临'.(int)($index)+1,
                'employee_id' =>'201310210'.(int)($index)+1,
                'idcarnumber' =>'370123199501296212',
                'address' =>"济宁市曲阜市杏坛路一路济宁学院",
                'telephone' =>'13969014530',
                'icon_location'=>"",
                'updated_at' => date('Y-m-d H:i:s'),
                'created_at' =>  date('Y-m-d H:i:s')
            ]);
        }
    }
}
