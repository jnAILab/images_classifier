<?php

use Illuminate\Database\Seeder;
class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = array('水池', '饮水机', '消防栓', '水龙头', '水管', '风扇', '插排', '电线', '暖气片', '暖气管道', '暖气井道');
        foreach($categories as $index => $category){
            DB::table('category')->insert([
                'category_id'=>'C_'.(int)($index)+1,
                'category_name' => $category,
                'category_location' => 'public/image/'.$category.'/',
                'created_at' =>date('Y-m-d H:i:s'),
                'updated_at' =>date('Y-m-d H:i:s'),
                'is_del'=>0,
            ]);
           // if(!file_exists('public/'.$category)){
               // mkdir('public/'.$category);
            //}
        }
    }
}
