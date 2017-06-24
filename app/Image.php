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
            //判断当前用户是否已经被分配了任务（预分类的图片不算做已被分配的任务）
           $imageCheckResult = $this->checkImagesIdByTask($user->user_id);
            if($imageCheckResult === false){
                //用户未分配任何任务。
                //判断当前用户是否为新用户
                $userCheckResult = $this->checkUserImageInformation($user->user_id);
                if($userCheckResult === true){
                    //当前用户未因注册用户，即无标记信息。则在所有的图片库里面随机抽取50张图片
                    //（50张图片= 20张预分类图片+30张正式分类图片。若该用户注册时，预分类的图片已被分配完毕，则分配50张随机图片）
                    return $this->randomGetImageIds($user->user_id);
                }else{
                    //根据用户的喜好分配200张图片
                    return $this->getImagesBySocket($user->user_id);
                }
            }else{
                return $imageCheckResult;//返回图片ids
            }
            return null;
       }

       public function getImagesBySocket($user_id){
           $host = 'tcp://192.168.2.6:12307';
           $fp = stream_socket_client ( $host, $errno, $error, 20 );
           if(!$fp){
               echo "$error ($errno)";
           }else{
               fwrite ($fp,$user_id);
               while (!feof($fp))
               {
                   $image_ids = fgets($fp); #获取服务器返回的内容
               }
               fclose ($fp);
           }
           print_r($image_ids);
       }

       public function checkUserImageInformation($user_id){
            $result = DB::table('image_label')->select('auto_id')->where('users_added','like',"%$user_id%")->get();
            //return $result;
            if(count($result)>0){
                return false;
            }else{
                return true;
            }
       }


        /**
         * @author killer 2017年6月4日01:48:11
         * 检查用户被分配的任务中图片是否全部为未标记图片
         * @param $user_id
         * @return array
         */
       public function checkImagesIdByTask($user_id){
            //获取用户已被分配的任务
           $imagesId = $this->getAssignTask($user_id);

           $result = DB::table('image')->select('status')->whereIn('image_id',$imagesId)->get();
           //将所有查询到的status结果相加到一起，若大于0则图片中包含已被分类的图片，则认为该用户已经被分配了任务。
           $flag = 0;
           foreach($result as $image){
               $flag += $image->status;
           }
           if(!$flag){
               //用户未被分配任务
                return false;
           }else{
               //已经被分配了任务。
               return $imagesId;
           }
       }

       function getAssignTask($user_id){
           $imagesId = array();
           $tables_name = Common::generateDatabaseNamesByClientId($user_id);
           //return $tables_name;
           foreach($tables_name as $table_name){
               $imagesIdInTable = DB::table($table_name)->select('image_id')->where('user_id','=',$user_id)->where('status','=',0)->get();
               foreach($imagesIdInTable as $image_id){
                   $imagesId[] = $image_id->image_id;
               }
           }
           return $imagesId;
       }



       public function randomGetImageIds($user_id){
           //随机获取图片
           //首先获取当前用户已经被分配了的预分配图片的任务
           $imagesId = $this->getAssignTask($user_id);
           if(count($imagesId)>=50){
                //若当前用户已经被分配了超过50张图片，则直接将图片id集push出去
               return $imagesId;
           }else{
               $totalImageNumber = DB::table('image')->count();
               //否则计算需要补充的图片个数。
               $needImageNumber = 50-(int)(count($imagesId));
               //根据上面计算到的个数，随机获取图片
               $randomIndex=array();
               while(count($randomIndex)<$needImageNumber){
                   $randomIndex[] = mt_rand(1,$totalImageNumber);
                   $randomIndex = array_unique($randomIndex);
                   $randomIndex = array_diff($randomIndex,$imagesId);
               }
               $randomImages = DB::table('image')->select('image_id')->whereIn('auto_id',$randomIndex)->get();
               foreach($randomImages as $image){
                   $imagesId[] = $image->image_id;
               }
               return $imagesId;
           }

       }

        /**
         * 显示图片标记信息的函数
         * @param $id
         *
         * @return $info
         * */
        public function getImageMarkedInformation($id){
            $info = DB::table('image_label')
                ->leftJoin('label', 'image_label.label_id', '=', 'label.label_id')
                ->select('image_label.image_id', 'label.label_name',"image_label.label_id","image_label.like_number")
                ->where('image_id',$id)
                ->get();
            return $info;
        }
        /**
         * 获取某个类别里面未被标记的图片列表
         * @param $category
         *
         * @return $info
         * */
        public function getImageUnmarkedList($category){
            if(empty($category)){
                $info = Image::leftJoin('image_label', 'image.image_id', '=', 'image_label.image_id')
                    ->where('image_label.label_id',null)
                    ->get();
            }else{
                $info = Image::leftJoin('image_label', 'image.image_id', '=', 'image_label.image_id')
                    ->where('image.category_id',$category)
                    ->where('label_id',null)
                    ->get();
            }
            return $info;
        }
        /**
         * 获取某个类别里面已被标记的图片列表
         * @param $category
         *
         * @return $info
         * */
        public function getImageMarkedList($category){
            if(empty($category)){
                $info = Image::leftJoin('image_label', 'image.image_id', '=', 'image_label.image_id')
                    ->where('label_id','!=',null)
                    ->get();
            }else{
                $info = Image::leftJoin('image_label', 'image.image_id', '=', 'image_label.image_id')
                    ->where('category_id',$category)
                    ->where('label_id','!=',null)
                    ->get();
            }
            return $info;
        }
	}
?>