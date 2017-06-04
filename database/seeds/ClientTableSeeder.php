<?php

use Illuminate\Database\Seeder;
class ClientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clients = DB::table('users')->where('status','client')->get();
        foreach($clients as $index => $client){
            DB::table('clients')->insert([
                'user_id'=>$client->user_id,
                'realname' => '李伟'.(int)($index)+1,
                'idcarnumber' => '370830199511076530',
                'address' =>"济宁市曲阜市杏坛路一路济宁学院",
                'telephone' =>'13011711238',
                'user_points'=>'300',
                'like_image_class'=>json_encode(array('C_1','C_3','C_4','C_7')),
                'icon_location'=>"",
                'updated_at' => date('Y-m-d H:i:s'),
                'created_at' =>  date('Y-m-d H:i:s')
            ]);
        }
    }
}
