<?php
	namespace App;
	use App\Client;
	use App\Common;
	use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\DB;
	class Image extends Model{
		protected $table = 'image';  //指定表名
		protected $primaryKey = 'auto_id';  //指定主键
		public $timestamps = false;  //关闭自动添加时间
		protected $guarded = ['auto_id'];  //不可批量添加的字段（黑名单）

        /**
         * @author killer 2017年6月4日01:48:48
         * 根据用户的信息推送一个图片
         * @param User $user
         * @return bool or image_id
         */
       public function pushImage(User $user){
            if($user->status != 'client'){
                //检查一下被分配任务的用户身份是否在用户
                return false;
            }
            $client = Client::select('like_image_class')->where('user_id','=',$user->user_id)->first();
            $like_image_class =json_decode($client->like_image_class,true);
            //获取用户感兴趣的范围内所有类别的照片id集合
            $allImagesIdInCategories = $this->getImagesIdByCategory($like_image_class);//用户感兴趣的所有类别里面的图片id集合
            //获取用户已经被分配的所有照片的集合
            $allImagesIdInTask = $this->getImagesIdByTask($user->user_id);
            //获取能分配的照片候选列表
            $candidacyOfImage = array_values(array_diff($allImagesIdInCategories, $allImagesIdInTask));
            //return count($candidacyOfImage);
            if(count($candidacyOfImage)== 0){
                return null;//没有看推送的图片
            }
            $randomIndex = rand(0,count($candidacyOfImage)-1);//生成一个随机数来获取图片
           return $candidacyOfImage[$randomIndex];
       }

        /**
         * @author killer 2017年6月4日01:23:25
         * 根据板块信息获取指定板块内所有的照片id集合
         * @param $image_categories
         * @return array
         */
       public function getImagesIdByCategory($image_categories){
           $imagesId = array();
           foreach($image_categories as $category){
               $images = $this->select('image_id')->where('category_id','=',$category)->get();
               foreach($images as $image_id){
                   $imagesId[] = $image_id->image_id;
               }
           }
            return $imagesId;
       }

        /**
         * @author killer 2017年6月4日01:48:11
         * 获取用户已经被分配的所有照片的集合
         * @param $user_id
         * @return array
         */
       public function getImagesIdByTask($user_id){
           $imagesId = array();
           $tables_name = Common::generateDatabaseNamesByClientId($user_id);
           //return $user_id;
           foreach($tables_name as $table_name){
               $imagesIdInTable = DB::table($table_name)->select('image_id')->where('user_id','=',$user_id)->get();
               foreach($imagesIdInTable as $image_id){
                   $imagesId[] = $image_id->image_id;
               }
           }
            return $imagesId;
       }
	}
?>