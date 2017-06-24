<?php
	namespace App\Http\Controllers;
	
	use App\Image_Label;
    use App\Task;
	use App\Common;
	use App\Image;
    use Tymon\JWTAuth\Facades\JWTAuth;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\DB;
	
	class ImageController extends Controller{
		/**
		*
		*@author 范留山
		*创建一个任务，用户喜欢的图片类型中随机选出一个图片推送出去
		*
		*@param  sendUserId ：md5加密的用户id
		*@param  sendCategoryId ：图片类型id
		*@
		*@return  json数据  {
		*						'resultCode'=>0,
		*						'resultMsg'=>‘success’,
		*						'data'=>{
		*							'image_id' => 图片id,
		*							'image_location'=>图片的地址,
		*						}
		*					};
		*@todo  1.用户是否可以有重复的任务，如：用户第二次标记某一个图片（当前可以有）；2.传参、返回内容的修改；
		*/
		function createTasks($userId,$imagesIds){
			$task = new Task();
            DB::beginTransaction();
			foreach($imagesIds as $imagesId){
                $is_created = $task -> createTaskMarkImage($userId,$imagesId);
                if(!$is_created){
                    DB::rollback();//事务回滚
                    return false;
                }
            }
            DB::commit();
			return true;
		}
        /**
         *
         *@author killer 2017年6月2日20:31:29
         *根据用户的喜好推出一个图片
         *
         *@param  Request $request
         *@return  json
         * {
            "ResultCode": 1,
            "ResultMsg": "push successful",
            "Data": {
                "image_id": "imageId3"
            }
        }
         */
         public function pushImageToUser(Request $request){
            $imageObj = new Image();
            $user = JWTAuth::parseToken()->authenticate();
            $image_ids = $imageObj->pushImage($user);
            if($image_ids === null){
                return Common::returnJsonResponse(0,'image\'s set is null','null');
            }

            $task_id = $this->createTasks($user->user_id,$image_ids);
            if($task_id === false){
                return Common::returnJsonResponse(0,'failed to create a task','null');
            }
            $images = Image::select('image_location','image_id')->whereIn('image_id',$image_ids)->get();
            $message = $images->toArray();

            if($image_ids === false){
                return Common::returnJsonResponse(0,'failed to push a image','null');
            }else{
                return Common::returnJsonResponse(1,'push successful',$message);
            }
        }

        /**
         * 显示图片标记信息的函数
         *
         * */
        public function getImageMarkedInformation(Request $request){
            $imageId=$request -> input("sendImageId");
            //$user = JWTAuth::parseToken()->authenticate();
            $imageObj = new Image();
            $data = $imageObj->getImageMarkedInformation($imageId);
            return Common::returnJsonResponse(1,'push successful',$data);
        }
        /**
         * 获取某个类别里面未被标记的图片列表
         * */
        public function getImageUnmarkedList(Request $request){
            $imageId=$request -> input("sendCategoryId");
            //$user = JWTAuth::parseToken()->authenticate();
            $imageObj = new Image();
            $data = $imageObj->getImageUnmarkedList($imageId);
            return Common::returnJsonResponse(1,'push successful',$data);
        }
        /**
         * 获取某个类别里面已被标记的图片列表
         * */
        public function getImageMarkedList(Request $request){
            $imageId=$request -> input("sendCategoryId");
            //$user = JWTAuth::parseToken()->authenticate();
            $imageObj = new Image();
            $data = $imageObj->getImageMarkedList($imageId);
            return Common::returnJsonResponse(1,'push successful',$data);
        }
        /**
         * @author dain 2017.6.4 15:00
         * 根据图片id 将图片移动到以当前用户user_id命名的文件夹进行打包
         * @param $request
         *
         * @return   json
         * {
        "ResultCode": 'true',
        "ResultMsg": "zip successful",
        "Data": {
        $downUrl
        }
         * }
         * ps：请在linux上安装zip程序
         * ps：请在linux上安装zip程序
         * ps：请在linux上安装zip程序
         */
        public function zipImage(Request $request)
        {
            $image_ids = $request->input('image_id');
            $user_id = JWTAuth::parseToken()->authenticate()->user_id;//获取当前用户$user_id,创建下载文件�?
            //$imageLocation = array();
            $getLocation = new Image();
            $imageLocation = $getLocation->getImageLocationInImage($image_ids);
            $newLocation = 'Image/download/' . $user_id;//下载文件夹打包地址
            if (!is_dir('Image/download/' . $user_id)) {
                mkdir("Image/download/" . $user_id);
            } else {//删除
                $allImage = glob($newLocation . '/*');
                foreach ($allImage as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                    //mkdir("Image/download/" . $user_id);
                }
            }
            foreach ($imageLocation as $location){
                exec("cp $location $newLocation");
            }
            if (!is_file($user_id . '.zip')) {
                //unlink($user_id . 'zip');
                //进行压缩
                $outputs = array();
                /*用php的exec执行Linux命令 括号里的字符串就是你在Linux命令窗口里敲的命令；
                第二个参数是linux执行该命令后返回的结果数组；
                linux执行返回的每一条结果依次存入该数组
                第三个参数是结果，如果执行成功，则Linux返回结果值为0，如果执行失败，则结果值不�?
                */
                exec("zip -r $user_id'.zip' $newLocation", $outputs, $rc);//exec调用linux命令
                if ($rc != 0) {
                    foreach ($outputs as $ko => $vo) {
                        echo "$vo<br/>";
                    }
                    return Common::returnJsonResponse('false', 'zip unsuccessful', null);
                } else {
                    $zipfile = $user_id . '.zip';
                    return Common::returnJsonResponse('true', 'zip successful', $zipfile);
                }
            }else {
                unlink($user_id . '.zip');
                //进行压缩
                $outputs = array();
                /*用php的exec执行Linux命令 括号里的字符串就是你在Linux命令窗口里敲的命令；
                第二个参数是linux执行该命令后返回的结果数组；
                linux执行返回的每一条结果依次存入该数组
                第三个参数是结果，如果执行成功，则Linux返回结果值为0，如果执行失败，则结果值不�?
                */
                exec("zip -r $user_id'.zip' $newLocation", $outputs, $rc);//exec调用linux命令
                if ($rc != 0) {
                    foreach ($outputs as $ko => $vo) {
                        echo "$vo<br/>";
                    }
                    return Common::returnJsonResponse('false', 'zip unsuccessful', null);
                } else {
                    $zipfile = $user_id . '.zip';//文件下载输出后删除相关文件
                    return Common::returnJsonResponse('true', 'zip successful', $zipfile);
                }
            }
        }
        /**
         * @author dain 2017.6.4 15:00
         * 操作数据库读取image_location
         * @param $image_ids
         * @return array
         */
        public function getImageLocationInImage($image_ids){
            $imageLocation = array();
            //dd($image_ids);
            foreach ($image_ids as $image_id){
                $image_locations = DB::table('image')
                    ->where('image_id', $image_id)
                    ->select('image_location')
                    ->get();
//               dd($image_locations);
                foreach ($image_locations as $image_location){
                    $imageLocation[] = $image_location->image_location;
                }
            }
            return $imageLocation;
        }
        public function getLabeledImageNumber(Request $request){
            $imageLabelObj = new Image_Label();
            $result = $imageLabelObj->all(); //获取全部的数据
            $allLabeledImageNumber = count($result);
            $result = $imageLabelObj->where('updated_at','>',date('Y-m-d H:i:s',strtotime("-7 day")))->get();//先获得一周内被操作过的图片列表。
            $labeledImageNumberForWeek = count($result);
            return Common::returnJsonResponse(1, 'query successful', array('allLabeledImageNumber'=>$allLabeledImageNumber,'labeledImageNumberForWeek'=>$labeledImageNumberForWeek));
        }
	}
?>