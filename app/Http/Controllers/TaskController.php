<?php
	namespace App\Http\Controllers;
	
	use App\Task;
	use App\Common;
	use Illuminate\Http\Request;
	use Tymon\JWTAuth\Facades\JWTAuth;


	class TaskController extends Controller{
        	/**
		*
		*@author 范留山
		*查看任务信息，
		*
		*@param  sendUserId ：md5加密的用户id
		*@param  sendImageId ：所查看任务的图片id
		*@
		*@return  json数据  {
		*						'image_lable'=>此图片标签的信息,
		*						'category'=>此图片的类别,
		*						'image_location'=>图片地址,
		*						'is_end' => 任务是否进行,
		*					}
		*@todo  传参，返回内容的修改
		*/
		public function getTasks(Request $request){
			$userId = JWTAuth::parseToken()->authenticate()->user_id;
			$imageId = $request->input("sendImageId");
			$taskId=$request->input('sendTaskId');
			$task = new Task();
			$data = $task -> getTasksInformation($userId,$taskId,$imageId);
			return Common::returnJsonResponse(1,'成功返回任务信息',$data);
		}
		
            /**
		*
		*@author 范留山
		*查看用户任务列表
		*
		*@param  sendUserId  前台发送的用户id
		*
		*@return  [
		*			"tasks" =>  [
		*							{
		*								'plate_information' => '任务列表'（板块信息，这个版块为任务列表）,
		*								'data' => [
		*									number=>点赞、踩的人数，
		*									label_name=>标签内容
		*								],
		*							},
		*						],
		*			];
		*@todo 传参，返回内容的修改
		*/
		public function getTaskList(Request $request){
			$userId = JWTAuth::parseToken()->authenticate()->user_id;
			$task = new Task();
			$data = $task -> getTaskList($userId);
			return Common::returnJsonResponse(1,'成功返回任务列表',$data);
		}
		
		

		
        	/**
		*
		*@author 范留山
		*更新任务信息，
		*
		*@param  sendUserId ：md5加密的用户id
		*@param  sendImageId ：要更新的图片id
		*@param  sendLabelByHand ：手写的标签名称
		*@param  sendLabelExistId ：已存在的标签的id
		*@param  sendAttitude ：对已有标签的看法（1顶，-2踩）
		*@
		*@return  json数据  {
		*					'result' => 'success',
		*					'user_assign_label'=>json  {标签one:1,标签two:-2},
		*					'user_assign_label_id'=>json  {标签one的id:1,标签two的id:-2}
		*					}
		*@todo  1.number的更新；2.传参，返回内容的修改
		*/
		public function updateTask(Request $request){
			$userId=$request->input('sendUserId');
			$imageId=$request->input('sendImageId');
			$labelByHand = $request->input('sendLabelByHand');
			$labelExistId = $request->input('sendLabelExistId');
			$attitude=$request->input('sendAttitude');
			$task = new Task();
			$result = $task -> updateTask($userId,$imageId,$labelByHand,$labelExistId,$attitude);
			return Common::returnJsonResponse(1,'成功更新任务信息','null');
		}

        	/**
		*
		*@author 范留山
		*删除任务信息，
		*
		*@param  sendUserId ：md5加密的用户id
		*@param  sendImageId ：所删除任务的图片id数组，如：['xxxxxx','yyyyyy']
		*@
		*@return  json数据  {
		*					'result' => 'success',
		*					'delect_number' => 删除的数目
		*					}
		*@todo  传参，返回内容的修改
		*/
		public function delectTask(Request $request){
			$userId = JWTAuth::parseToken()->authenticate()->user_id;
			$imageId=$request->input('sendImageId');
			$taskId=$request->input('sendTaskId');
			$task = new Task();
			$data = $task -> delectTask($userId,$imageId,$taskId);
			return Common::returnJsonResponse(1,'成功删除任务',$data);
		}


	}
?>