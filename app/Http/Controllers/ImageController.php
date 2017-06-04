<?php
	namespace App\Http\Controllers;
	
	use App\Task;
	use App\Common;
	use App\Image;
    use Tymon\JWTAuth\Facades\JWTAuth;
	use Illuminate\Http\Request;
	
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
		function createTasks($userId,$imagesId){
			$task = new Task();
			$is_created = $task -> createTaskMarkImage($userId,$imagesId);
			return $is_created;
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
            $image_id = $imageObj->pushImage($user);
            if($image_id === null){
                return Common::returnJsonResponse(0,'image\'s set is null','null');
            }
            $task_id = $this->createTasks($user->user_id,$image_id);
            //var_dump($task_id);
            if($image_id === false){//用户身份错误
                return Common::returnJsonResponse(0,'failed to push a image','null');
            }else if($task_id === false){//任务创建失败
                return Common::returnJsonResponse(0,'failed to create a task','null');
            }else{
                return Common::returnJsonResponse(1,'push successful',array('image_id'=>$image_id,'task_id'=>$task_id));
            }
        }
	}
?>