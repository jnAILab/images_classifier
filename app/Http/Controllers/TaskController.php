<?php
	namespace App\Http\Controllers;
	
	use App\Task;
	use App\Common;
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
							'ResultCode' : 1（1成功，0失败）,
					            'ResultMsg'  : '成功返回任务信息',
					            'Data' : {
									'image_lable'=>此图片标签的信息,
									'category'=>此图片的类别,
									'image_location'=>图片地址,
									'is_end' => 任务是否进行,
								}
							}
		*
		*@todo  传参，返回内容的修改
		*/
		public function getTasks(Request $request){
			$userId=$request->input('sendUserId');
			$imageId=$request->input('sendImageId');
			$data = Task::getTasksInformation($userId,$imageId);
			return Common::returnJsonResponse(1,'成功返回任务信息',$data);
		}
		
            /**
		*
		*@author 范留山
		*查看用户任务列表
		*
		*@param  sendUserId  前台发送的用户id
		*
		*@return {
					'ResultCode' : 1（1成功，0失败）,
			            'ResultMsg'  : '成功返回任务列表',
			            'Data' : {
							'plate_information' : '任务列表'（板块信息提示，这个版块为任务列表）,
							'data' : [
								number:点赞、踩的人数，
								label_name:标签内容
							],
						}
					}
		*
		*@todo 传参，返回内容的修改
		*/
		public function getTaskList(Request $request){
			$userId=$request->input('sendUserId');
			$data = Task::getTaskList($userId);
			//return $result;
			return Common::returnJsonResponse(1,'成功返回任务列表',$data);
		}
		
		
		/**
		*
		*@author 范留山
		*创建一个任务，用户喜欢的图片类型中随机选出一个图片推送出去
		*
		*@param  sendUserId ：md5加密的用户id
		*@param  sendCategoryId ：图片类型id
		*@
		*@return {
					'ResultCode' : 1（1成功，0失败）,
			            'ResultMsg' :  '成功创建任务',
			            'Data' : null
						}
					}
		*
		*@todo  1.用户是否可以有重复的任务，如：用户第二次标记某一个图片（当前可以有）；2.传参、返回内容的修改；
		*/
		public function createTasks(Request $request){
			$userId=$request->input('sendUserId');
			$imagesId=$request->input('sendCategoryId');
			$data = Task::createTaskMarkImage($userId,$imagesId);
			return Common::returnJsonResponse(1,'成功创建任务','null');
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
		*@return  {
					'ResultCode' :1（1成功，0失败）,
			            'ResultMsg'  : '成功更新任务信息',
			            'Data' ;null
						}
					}
		*
		*@todo  1.number的更新；2.传参，返回内容的修改
		*/
		public function updateTask(Request $request){
			$userId=$request->input('sendUserId');
			$imageId=$request->input('sendImageId');
			$labelByHand = $request->input('sendLabelByHand');
			$labelExistId = $request->input('sendLabelExistId');
			$attitude=$request->input('sendAttitude');
			$result = Task::updateTask($userId,$imageId,$labelByHand,$labelExistId,$attitude);
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
		*@return {
					'ResultCode' :1（1成功，0失败）,
			            'ResultMsg' : '成功删除任务',
			            'Data' :{
							number : 2（删除数目）
							],
						}
					}
		*@todo  传参，返回内容的修改
		*/
		public function delectTask(Request $request){
			$userId=$request->input('sendUserId');
			$imageId=$request->input('sendImageId');
			$data = Task::delectTask($userId,$imageId);
			return Common::returnJsonResponse(1,'成功删除任务',$data);
		}
	}
?>