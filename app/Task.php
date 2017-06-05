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
                $task = DB::table($table_name)->select('task_id')->where('user_id','=',$user_id)->where('image_id','=',$image_id)->first();
                return $task->task_id;
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
			foreach ($tables as $table){
				$all_tables[$number_allTables]=$table->table_name;
				$number_allTables+=1;
			}
			$number_task_tables=0;
			$user_id_last_str=substr($userId,-1);//用户id最后一位字符
			
			//处理获得一定存在此用户任务的表；表名处理成数组；
			foreach($all_tables as $judge_table){
				if(strpos($judge_table,'task') !== false){
					if(strpos($judge_table,$user_id_last_str)!==false){
						$task_tables[$number_task_tables]=$judge_table;
						$number_task_tables+=1;
					}
				}
			}
			
			//循环查找用户所有任务；储存为数组
			$number_tasks=0;
			foreach($task_tables as $task_table){
				$tasks = DB::table($task_table)
					->select('image_id')
					->where('user_id',$userId)
					->get();
				if(count($tasks)>0){
					foreach($tasks as $task){
						$all_tasks[$number_tasks]=$task->image_id;
						$number_tasks+=1;
					}
				}
			}
			//查找标签信息（点赞或踩的人数，标签内容）
			$label_informations = Label::select('number','label_name')
				->whereIn('image_id',$all_tasks)
				->get();
			
//			//获取图片信息（地址、类型id）
//			$image_informations = Image::select('image_location','category_id')
//				->whereIn('image_id',$all_tasks)
//				->get();
//			
//			
//			//将所有图片类型id（category_id）去除重复后，处理成一个数组
//			$category_number=0;
//			foreach($image_informations as $image_information){
//				if($category_number==0 || in_array($image_information->category_id,$category_ids,false)==false){//括号内第三个参数（ture或false）如果为true 类型一并检查
//					$category_ids[$category_number]= $image_information->category_id;
//					$category_number+=1;
//				}
//			}
//		
//		
//			//查询类别信息
//			$category_informations = Category::select('category_id','category_name','category_location')
//				->whereIn('category_id',$category_ids)
//				->get();
//			
//			// 类别名称 与 图片信息 对应起来	(未完成)
//			$number_informations=0;
//			foreach($category_informations as $category_information){
//				$result_informations[$number_informations]=$category_information;
//				$number_task=0;
//				foreach($all_tasks as $task){
//				//	if($image_informations->category_id == $category_informations->category_id){
//						$result_informations[$number_informations][$number_task]=$task;
//						$number_task += 1;
//				//	}
//				}
//				$number_informations+=1;
//			}
			
			return $label_informations;
		}
		
		
		/**
		*
		*@author 范留山
		*查看任务信息，
		*
		*@param  $userId ：md5加密的用户id
		*@param  $imageId ：所查看任务的图片id
		*@
		*@return  array(
					'image_lable'=>$information->user_assign_label,
					'category'=>$category['category_name'],
					'label_information' => json_encode($label_informations),
					);
		*@todo  传参，返回内容的修改
		*/
		public function getTasksInformation($userId,$imageId){
			
			//先进行表的选择
			$table=substr($userId,-1)."_".substr($imageId,-3)."_task";
			
			//查找此表中，此用户id和此图片id  对应的user_assign_label
			$information = DB::table($table)
				->select('user_assign_label')
				->whereRaw('image_id = ? and user_id = ?',[$imageId,$userId] )
				->first();
			
			//image表中查找相关信息
			$image_information = Image::select('*')
				->where('image_id',$imageId)
				->first();
			
			//类别表中查找相关信息
			$category = Category::select('category_name')
				->where('category_id',$image_information['category_id'])
				->first();
			
			//查找标签信息（点赞或踩的人数，标签内容）
			$label_informations = Label::select('number','label_name')
				->where('image_id',$imageId)
				->get();
			
			return array(
					'image_lable'=>$information->user_assign_label,
					'category'=>$category['category_name'],
					'label_information' => json_encode($label_informations),
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
		public function delectTask($userId,$imageIds){
			
			$delect_number = 0;
			foreach($imageIds as $imageId){
				//获取user_id最后一位，image_id 后三位
				$table=substr($userId,-1)."_".substr($imageId,-3)."_task";//substr('字符串'，获取前（后）几位)
					
				//删除一条记录
				$delect_result = DB::table($table)
					->whereRaw('user_id = ? and image_id =?',[$userId,$imageId])
					->delete();
				$delect_number+=$delect_result;
			}
			return	array('delect_number' => $delect_number);
		}
	}
?>