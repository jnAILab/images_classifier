<?php

use Illuminate\Database\Seeder;
class ImageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //DB::statement('SET NAMES GBK');
        $categoryPath = "public/image";
        $filePathRoot = "public/image";
        $categories = array('C_1'=>'水池',
                            'C_2'=>'饮水机',
                            'C_3'=>'消防栓',
                            'C_4'=>'水龙头',
                            'C_5'=>'水管',
                            'C_6'=>'风扇',
                            'C_7'=>'插排',
                            'C_8'=>'电线',
                            'C_9'=>'暖气片',
                            'C_10'=>'暖气管道',
                            'C_11'=>'暖气井盖');
        foreach($categories as $category_id =>$category){
            $imagesPath = $categoryPath.'/'.$category;
            $images=scandir($imagesPath);
            foreach($images as $image){
                if($image!='.'&& $image!='..'){
                    $filePath = $filePathRoot .'/'.$category. '/' . $image;
                    $myfile = fopen($filePath, "r");
                    $image_id = md5(fread($myfile, filesize($filePath)));
                    fclose($myfile);
                    //rename($filePath,$filePathRoot .'/'.$category_id. '/'.$image_id.'.jpg');
                    DB::table('image')->insert([
                        'image_id' => $image_id,
                        "category_id" => $category_id,
                        'image_location' => $filePathRoot .'/'.$category_id. '/' . $image,
                        'image_author_id' => '5932e222209bf',
                        'upload_time' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                        'start_time' => date('Y-m-d H:i:s'),
                        'end_time' => date("Y-m-d", strtotime("+1 months"))
                    ]);
                    $this->command->getOutput()->writeln("<info>" . $image . "</info> ");
                }
            }








        }
    }
}
