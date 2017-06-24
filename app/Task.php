<?php
	namespace App;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Support\Facades\DB;

	class Task extends Model {
		
		/**
		*
		*@author 范留山
		*创建一个任务，用户喜欢的图片类型中随机选出一个图片推送出去
		*
		*@param  $userId ：md5加密的用户id
		*@param  $category_id ：图片类型id
		*@
		*@return true;
		*@todo  1.用户是否可以有重复的任务，如：用户第二次标记某一个图片（当前可以有）；2.传参、返回内容的修改；
		*/
		public function createTaskMarkImage($user_id,$image_id){
            //根据用户名和图片id获取表名
            $table_name = Common::generateDatabaseNamesByClientIdAndImageId($user_id,$image_id);
            //检查一下这个表是否存在，如果不存在就创建出来。
            Common::checkDatabaseByTableName($table_name);

            //$table(A_BBB_task)表中插入任务信息
            $task_result = DB::table($table_name)->insert([
                'task_id'=> uniqid(),
                'user_id' => $user_id,
                'image_id' => $image_id
            ]);
            if($task_result){
                //插入数据成功
                #$task = DB::table($table_name)->select('task_id')->where('user_id','=',$user_id)->where('image_id','=',$image_id)->first();
                #return $task->task_id;
                return true;
            }
            return false;//否则插入数据失败
		}


		/**
		*
		*@author 范留山
		*获得用户任务列表
		*
		*@param  $userId  前台发送的用户id
		*
		*@return  $label_informations;
		*@todo 传参，返回内容的修改
		*/
		public function getTaskList($userId){
			
			//获取此数据库所有表名
			$tables=DB::select("select table_name from information_schema.tables where table_schema='images_classifier'");

			//$tables 为 stdClass Object 对其处理取值
			$number_allTables=0;
			$all_tables=array();
			foreach ($tables as $table){
				$all_tables[$number_allTables]=$table->table_name;
				$number_allTables+=1;
			}
			$number_task_tables=0;
			$user_id_last_str=substr($userId,-1);//用户id最后一位字符
			
			//处理获得一定存在此用户任务的表；表名处理成数组；
			$task_tables=array();
			foreach($all_tables as $judge_table){
				if(strpos($judge_table,'task') !== false){
					if(strpos($judge_table,$user_id_last_str)!==false){
						$task_tables[$number_task_tables]=$judge_table;
						$number_task_tables+=1;
					}
				}
			}

			//循环查找用户所有任务；将任务的图片id储存为数组
			$all_information = array();
			foreach($task_tables as $task_table){
				$tasks = DB::table($task_table)
					->join('image','image.image_id',$task_table.'.image_id')
					->select(
						$task_table.'.task_id',
						$task_table.'.image_id',
						$task_table.'.user_assign_label',
						$task_table.'.user_assign_label_id'
					)
					->where($task_table.'.user_id',$userId)
					->where('image.is_del','0')
					->get();

				if(count($tasks)>0){
					foreach($tasks as $task){
						//判断任务状态
						$imageInfomration = Image::select('image_location','end_time')
							->where('image_id', $task->image_id)
							->first();
						$now_time = date("Y-m-d H:i:s");
						if ($imageInfomration->end_tiem <= $now_time) {
							$state = 1;
						} else {
							$state = 0;
						}

						if(json_decode($task->user_assign_label_id,true)!=null) {

							$label_ids = array_keys(json_decode($task->user_assign_label_id, true));
							$label_names = array_keys(json_decode($task->user_assign_label, true));

							$number = 0;
							foreach ($label_ids as $a_label_id) {

								$like = Image_Label::select('like_number')
									->where('label_id', $a_label_id)
									->first();

								$all_information[$task->task_id][$a_label_id]['image_id'] = $task->image_id;
								$all_information[$task->task_id][$a_label_id]['image_location'] = $imageInfomration->image_location;
								$all_information[$task->task_id][$a_label_id]['label_name'] = $label_names[$number];
								$all_information[$task->task_id][$a_label_id]['like_number'] = $like->like_number;
								$all_information[$task->task_id][$a_label_id]['state'] = $state;

								$number += 1;

							}
						}else{
							$all_information[$task->task_id]['image_id']=$task->image_id;
							$all_information[$task->task_id]['image_location'] = $imageInfomration->image_location;
							$all_information[$task->task_id]['state']=$state;
						}
					}
				}
			}
			return $all_information;
		}
		
		
		/**
		*
		*@author 范留山
		*查看任务信息，
		*
		*@param  $userId ：md5加密的用户id
		*@param  $taskId ：所查看任务的id
		*@
		*@return  array(
					'image_lable'=>$information->user_assign_label,
					'category'=>$category['category_name'],
					'label_information' => json_encode($label_informations),
					);
		*@todo  传参，返回内容的修改
		*/
		public function getTasksInformation($userId,$taskId,$imageId){
			//先进行表的选择
			$table=substr($userId,-1)."_".substr($imageId,-3)."_task";

			//查找此表中，此用户id和此图片id  对应的user_assign_label
			$information = DB::table($table)
				->select('user_assign_label',"user_assign_label_id")
				->where('task_id',$taskId )
				->first();

            //image表中查找相关信息
            $image_information = Image::select('image_location')
                ->where('image_id',$imageId)
                ->first();
            if($information->user_assign_label==null){//如果此任务还没有标签
                return array(
                    'image_location'=>$image_information['image_location'],//加入此图片地址
                    'label_information' => null
                );
            }else{
                $all_label_name = array_keys(json_decode($information->user_assign_label,true));
                $all_label_id=array_keys(json_decode($information->user_assign_label_id,true));
            }
			$number=0;
			$all_labelInformation=array();
			$labelInformation=array();
			foreach($all_label_name as $label_name){
				$labelInformation['label_id']=$all_label_id[$number];
				$labelInformation['label_name']=$label_name;
				$labelInformation['like_number']=json_decode($information->user_assign_label,true)[$label_name];
				$all_labelInformation[]=$labelInformation;
				$number+=1;
			}



			//查找标签信息（点赞或踩的人数，标签内容）
			/*$label_informations = Label::join("image_label","image_label.label_id","label.label_id")
				->select('image_label.like_number','label.label_name')
				->where('image_id',$imageId)
				->get();*/

			return array(
/*					'image_lable'=>array_keys(json_decode($information->user_assign_label,true)),*/
                    'image_location'=>$image_information['image_location'],//加入此图片地址
					'label_information' => $all_labelInformation //json_decode($information->user_assign_label_id,true),
					);
		}
		
		/**
		*
		*@author 范留山
		*更新任务信息，
		*
		*@param  $userId ：md5加密的用户id
		*@param  $imageId ：要更新的图片id
		*@param  $labelByHand ：手写的标签名称
		*@param  $labelExistId ：已存在的标签的id
		*@param  $attitude ：对已有标签的看法（1顶，-2踩）
		*@
		*@return  true
		*@todo  传参，返回内容的修改
		*/

        public function updateTask($userId,$imageId,$labelByHand,$labelExistId,$attitude){
            //查询此标签是否存在，如果存在返回信息，不存在插入数据并返回信息
            if ($labelByHand!=null){
                $label_information_hand=Label::firstOrCreate(['label.label_id'=>md5($labelByHand)],
                    [
                        'label.label_name'=>$labelByHand,
                    ]);
                Image_Label::firstOrCreate(['label_id'=>md5($labelByHand)],
                    [
                        'image_id'=>$imageId,
                        "like_number"=>1
                    ]);
            }
            //查找图片标签名字
            $label_exist_name=Label::join("image_label","image_label.label_id","label.label_id")
                ->select('label.label_name')
                ->whereRaw('image_label.image_id = ? and image_label.label_id =?',[$imageId,$labelExistId] )
                ->first();

            //获取user_id最后一位，image_id 后三位
            $table=substr($userId,-1)."_".substr($imageId,-3)."_task";//substr('字符串'，获取前（后）几位)

            //取出要更新字段的值（更新后插入）
            $updates=DB::table($table)
                ->select('user_assign_label','user_assign_label_id')
                ->whereRaw('image_id = ? and user_id =?',[$imageId,$userId] )
                ->first();
            //处理需更新的数据
            $user_assign_label=json_decode($updates->user_assign_label,true);
            $user_assign_label_id=json_decode($updates->user_assign_label_id,true);
            if($labelByHand!=null){//如果存在手写的标签
                $user_assign_label[$labelByHand]=1;
                $user_assign_label_id[$label_information_hand->label_id]=1;
            }
            if($labelExistId!=null){//如果存在踩或者顶的标签id

                $result=Image_Label::where('label_id',$labelExistId)
                    ->increment('like_number',$attitude);
                $user_assign_label[$label_exist_name->label_name]+=$attitude;
                $user_assign_label_id[$labelExistId]+=$attitude;
            }

            //更新数据
            $updata_result = DB::table($table)
                ->whereRaw('user_id =? and image_id = ?',[$userId,$imageId])
                ->update([
                    'user_assign_label' => json_encode($user_assign_label),
                    'user_assign_label_id' => json_encode($user_assign_label_id)
                ]);

            return true;
        }
		
		
		/**
		*
		*@author 范留山
		*删除任务信息，
		*
		*@param  $userId ：md5加密的用户id
		*@param  $imageIds ：所删除任务的图片id数组，如：['xxxxxx','yyyyyy']
		*@
		*@return  array('delect_number' => $delect_number);
		*@todo  传参，返回内容的修改
		*/
		public function delectTask($userId,$imageIds,$taskIds){
			
			$delect_number = 0;
			foreach($imageIds as $imageId){
				//获取user_id最后一位，image_id 后三位
				$table=substr($userId,-1)."_".substr($imageId,-3)."_task";//substr('字符串'，获取前（后）几位)
					
				//删除一条记录
				$delect_result = DB::table($table)
					->whereIn('task_id',$taskIds)
                    ->where('user_id',$userId)
					->delete();
				$delect_number+=$delect_result;
			}
			return	array('delect_number' => $delect_number);
		}
	}
?>