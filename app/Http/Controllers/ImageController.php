<?php
	namespace App\Http\Controllers;
	
	use App\Image_Label;
    use App\Task;
	use App\Common;
	use App\Image;
    use App\User;
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
            $imageInfo = array();
            DB::beginTransaction();
            foreach($imagesIds as $imagesId){
                $is_created = $task -> createTaskMarkImage($userId,$imagesId);
                if($is_created === false){
                    DB::rollback();//事务回滚
                    return false;
                }
                $imageInfo[$imagesId] = $is_created;
            }
            DB::commit();
            return $imageInfo;
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
            //var_dump($image_ids);
            //return;
            $task_ids = $this->createTasks($user->user_id,$image_ids);
            if($task_ids === false){
                return Common::returnJsonResponse(0,'failed to create a task','null');
            }
            $images = Image::select('image_location','image_id')->whereIn('image_id',$image_ids)->get();
            $message = $images->toArray();
            foreach($message as $index =>$item){
                $message[$index][] =$task_ids[$item['image_id']];
            }
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
         *
         *@author 葛操 2017年6月2日20:31:29
         *上传图片并分配任务
         *
         *@param  Request $request
         *@return  json
         * {
        "ResultCode": 1,
        "ResultMsg": "上传成功",
        "Data": null
        }
         */
        public function upload(Request $request)
        {
            $task = new Task();
            //从user表中选取全部user_id，以便在创建任务
            $users = DB::table('users')->where('status','client')->select('user_id')->get()->toArray();
            $userArray = array();
            foreach ($users as $user)
            {
                array_push($userArray,$user->user_id);
            }
            //var_dump($randomkeys);
            $file = $request->file('zip');
            $filename =  $file->getClientOriginalName();
            $filenames = explode('.',$filename);
            $file_name = $filenames[0];
            $extend = $filenames[1];
            $tmp = md5('tmp');
            $path = 'Image/'.$tmp.'/';
            $zipname = $file_name.'.'.$extend;
            $file->move($path,$zipname);
            if($newzipname = str_replace('(','',$zipname))
            {
                $newzipname1 = str_replace(')','',$newzipname);
                rename($path.$zipname,$path.$newzipname1);
                $zipname = $newzipname1;
            }
            //$zipfile = zip_open('Image/'.$file_name.$zipname);
            $bool = system('unzip '.$path.$zipname.' -d '.$path);
            system('rm -f '.$path.$zipname);
            $images = glob($path.'*.*');
            $extends = explode(",", "jpg,jpeg,png");
            $flag = 0;
            $flag1 = 0;
            date_default_timezone_set('PRC');
            //从image表中选取image_id 用于创建任务
            $image_ids = DB::table('image')->select('image_id')->get()->toArray();
            $image_idArray = array();
            foreach ($image_ids as $image_id)
            {
                array_push($image_idArray,$image_id->image_id);
            }
            //var_dump($image_idArray);
            foreach ($images as $image)
            {
                $imagename = basename($image);
                $image_names = explode('.',$imagename);
                $image_name = $image_names[0];
                $image_extend = $image_names[1];
                if(in_array($image_extend,$extends)){
                    $newImageName = md5($image);
                    $newImageNames = $newImageName.'.'.$image_extend;
                    $bool1 = rename($path.$image_name.'.'.$image_extend,'Image/'.$newImageName);
                    if(!(in_array($newImageName,$image_idArray)))
                    {
                        Image::create(
                            [
                                'image_id'=>$newImageName,
                                'image_location'=>'public/Image/'.$newImageNames,
                                'image_author_id'=>'5932e222209bf',
                                'upload_time'=>date("Y-m-d H:i:s"),
                                'updated'=>1
                            ]
                        );
                        //随机选取20个用户 并分配任务
                        $randomkeys = array_rand($userArray,3);
                        foreach ($randomkeys as $randomkey)
                        {
                            //var_dump($userArray[$randomkey]);
                            $result = $task->createTaskMarkImage($userArray[$randomkey],$newImageName);
                            //var_dump($result);
                        }
                    }



                    $flag1++;
                }else{
                    $flag++;
                    system('rm -f '.$path.$image_name.'.'.$image_extend);
                }
            }
            system('rm -rf Image/'.$tmp);
            if($flag == 0&&$flag1 > 0){
                return Common::returnJsonResponse(1,'上传成功',null);
            }else{
                return Common::returnJsonResponse(0,'上传失败，压缩包可能存在非图片！',null);
            }
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
            $user_id = JWTAuth::parseToken()->authenticate()->user_id;//获取当前用户$user_id,创建下载文件 ?

            //$imageLocation = array();
            $getLocation = new Image();
            $imageLocation = $getLocation->getImageLocationInImage($image_ids);
            $newLocation = 'download/' . $user_id;//下载文件夹打包地址


            if (!is_dir('download/' . $user_id)) {
                mkdir("download/" . $user_id);
            } else {//删除
                $allImage = glob($newLocation . '/*');
                foreach ($allImage as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                    //mkdir("Image/download/" . $user_id);
                }
            }
            foreach ($imageLocation as $location) {
                exec("cp $location $newLocation");//拷贝图片
            }

            if (!is_file($user_id . '.zip')) {
                //unlink($user_id . 'zip');
                //进行压缩
                $outputs = array();
                /*用php的exec执行Linux命令 括号里的字符串就是你在Linux命令窗口里敲的命令；
                第二个参数是linux执行该命令后返回的结果数组；
                linux执行返回的每一条结果依次存入该数组
                第三个参数是结果，如果执行成功，则Linux返回结果值为0，如果执行失败，则结果值不 ?
                */
                exec("zip -r $user_id'.zip' $newLocation", $outputs, $rc);//exec调用linux命令
                if ($rc != 0) {
                    foreach ($outputs as $ko => $vo) {
                        echo "$vo<br/>";
                    }
                    return Common::returnJsonResponse(0, 'zip unsuccessful', null);
                } else {
                    $zipfile = $user_id . '.zip';
                    return Common::returnJsonResponse(1, 'zip successful', $zipfile);
                }
            } else {
                unlink($user_id . '.zip');
                //进行压缩
                $outputs = array();
                /*用php的exec执行Linux命令 括号里的字符串就是你在Linux命令窗口里敲的命令；
                第二个参数是linux执行该命令后返回的结果数组；
                linux执行返回的每一条结果依次存入该数组
                第三个参数是结果，如果执行成功，则Linux返回结果值为0，如果执行失败，则结果值不 ?
                */
                exec("zip -r $user_id'.zip' $newLocation", $outputs, $rc);//exec调用linux命令
                if ($rc != 0) {
                    foreach ($outputs as $ko => $vo) {
                        echo "$vo<br/>";
                    }
                    return Common::returnJsonResponse(0, 'zip unsuccessful', null);
                } else {
                    $zipfile = $user_id . '.zip';//文件下载输出后删除相关文件
                    return Common::returnJsonResponse(1, 'zip successful', $zipfile);
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