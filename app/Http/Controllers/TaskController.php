<?php
	namespace App\Http\Controllers;
	
	use App\Task;
	use App\updateTaskMarkImage_model;
	use Illuminate\Http\Request;
	
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
			$userId=$request->input('sendUserId');
			$imageId=$request->input('sendImageId');
			$result = Task::getTasksInformation($userId,$imageId);
			return $result;
		}
		
            /**
		*
		*@author 范留山
		*获得用户任务列表
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
			$userId=$request->input('sendUserId');
			$result = Task::getTaskList($userId);
			return $result;
		}
		
		
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
		public function createTasks(Request $request){
			$userId=$request->input('sendUserId');
			$imagesId=$request->input('sendCategoryId');
			$result = Task::createTaskMarkImage($userId,$imagesId);
			return $result;
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
			$result = Task::updateTask($userId,$imageId,$labelByHand,$labelExistId,$attitude);
			return $result;
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
			$userId=$request->input('sendUserId');
			$imageId=$request->input('sendImageId');
			$result = Task::delectTask($userId,$imageId);
			return $result;
		}
	}
?>