<?php
/**
 * Created by PhpStorm.
 * User: n
 * Date: 2017/4/25 0025
 * Time: 19:50
 */

namespace App\Http\Controllers;



use App\Category;
use App\Client;
use App\Common;
use App\Admin;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class PersonController extends Controller
{
	/**
     *
     *@author 聂恒奥
     *
     * 修改密码，从前台接收newPassword和user_id
     *
     *
     */
    public function changePassword(Request $request){
        $Common = new Common();
        $user_id = JWTAuth::parseToken()->authenticate()->user_id;
//        $user_id = $request->input('user_id');
        $newPassword = $request->input('newPassword');
        $ResultCode = $Common->changePassword($user_id,$newPassword);
        if ($ResultCode){
            $ResultMsg = '成功';
            $ResultCode = 1;
        }
        else{
            $ResultMsg = '失败';
            $ResultCode = 0;
        }
        return Common::returnJsonResponse($ResultCode,$ResultMsg,null);
    }
    /**
     *
     *@author 聂恒奥
     *
     * 更加积分，从前台接收user_points和user_ids数组。
     *
     *
     */
    public function increaseUserPoints(Request $request){
//        $user_ids = $request->input('user_ids');
//        $user_points = $request->input('user_points');
        $user_id = JWTAuth::parseToken()->authenticate()->user_id;
        $user_points = $request->input('user_points');
        $user = new User();
        $ResultCode = $user->increaseUserPoints($user_id,$user_points);
        if ($ResultCode){
            $ResultMsg = '成功';
            $ResultCode = 1;
        }
        else{
            $ResultMsg = '失败';
            $ResultCode = 0;
        }
        return Common::returnJsonResponse($ResultCode,$ResultMsg,null);
    }
    /**
     *
     *@author 聂恒奥
     *
     * 修改信息，用户修改个人信息时从前台接收user_id和以对应字段名为名的变量；
     * 批量修改时从前台接收user_id数组。
     *
     *
     */
    public function updatePersonInformation(Request $request){
        $Common = new Common();
        $all = $request->all();
        $user_id = JWTAuth::parseToken()->authenticate()->user_id;
        $ResultCode = $Common->updatePersonInformation($all,$user_id);
        if ($ResultCode){
            $ResultMsg = '成功';
            $ResultCode = 1;
        }
        else{
            $ResultMsg = '失败';
            $ResultCode = 0;
        }
        return Common::returnJsonResponse($ResultCode,$ResultMsg,null);
    }
    public function adminUpdateInformation(Request $request){
        $Common = new Common();
        $all = $request->all();
        $user_id = $request->input('user_id');
        $ResultCode = $Common->adminUpdateInformation($all,$user_id);
        if ($ResultCode){
            $ResultMsg = '成功';
            $ResultCode = 1;
        }
        else{
            $ResultMsg = '失败';
            $ResultCode = 0;
        }
        return Common::returnJsonResponse($ResultCode,$ResultMsg,null);
    }


    /**
		*
		*@author 范留山
		*添加管理员，
		*
		*
		*@param  sendUsername ：用户名
		*@param  sendPassword ：密码
		*@param  sendEmployeeId ：工号
		*@param  sendEmail ：邮箱
		*@
		*@return  [
			            'ResultCode' => 1,
			            'ResultMsg'  => 添加管理员成功,
			            'Data' => null
			        ],$status
		*@todo  1.number的更新；2.传参，返回内容的修改
		*/
		public function addAdmin(Request $request){
			$password = $request->input('sendPassword');
			$email = $request->input('sendEmail');
			$addAdmin=new Admin();
			$addAdmin->addAdministrator($password,$email);
			return Common::returnJsonResponse(1,'添加管理员成功','null');
		}
		
		/**
		*@author 田荣鑫
		* 删除管理员
		*/
		public function deleteAdministrators(Request $request)
		{
			$user_id = $request ->input('user_id');//获取前台勾选的需要删除的管理员id，以users表中的user_id为标准
			$delAdmin = new Admin();
			$result = $delAdmin->deleteAdministrators($user_id);

			if ($result){
                $resultCode=0;
                $resultMsg='删除成功';
            }else{
                $resultCode=1;
                $resultMsg='删除失败';
            }
			return Common::returnJsonResponse($resultCode,$resultMsg,null);
		}

		/* *
		* auther 田荣鑫
		* 获取管理员列表
		*/
		public function getAdministratorList()
		{
			$getAdmin = new Admin();
			$result = $getAdmin->getAdministratorList();

			if ($result){
                $resultCode=0;
                $resultMsg='获取管理员列表成功';
            }else{
                $resultCode=1;
                $resultMsg='获取管理员列表失败';
            }
			return Common::returnJsonResponse($resultCode,$resultMsg, $result);
		}

		/**
		*@author 田荣鑫
		* 更改管理员密码
		*/
		public function alterAdminPsd(Request $request)
		{
            //$adminState = $request->input('adminState');
            $user_id = $request->input('user_id');
            if ($user_id == null) {
                $user_id = JWTAuth::parseToken()->authenticate()->user_id;
            }
			$newPassword = $request->input('newPassword');
			$alterPsd = new Admin();
			$result = $alterPsd->alterAdminPsd($user_id,app('hash')->make($newPassword));
            if ($result){
                $resultCode=0;
                $resultMsg='更改密码成功';
            }else{
                $resultCode=1;
                $resultMsg='更改密码失败';
            }
            return Common::returnJsonResponse($resultCode,$resultMsg,null);
        }
        /**
         *@author 聂恒奥
         * 获取用户个人信息
         */
        public function getPersonInformation(Request $request){
            $user_id = $request->input('user_id');
            $client = new Client();
            $personInformation = $client->getPerInformationToShow($user_id);
            if ($personInformation){
                $ResultMsg = '成功';
                $ResultCode = 1;
            }
            else{
                $ResultMsg = '失败';
                $ResultCode = 0;
            }
            return Common::returnJsonResponse($ResultCode,$ResultMsg,$personInformation);
        }

        /*
         * @auth 范留山
         * 显示用户列表
         * **/
        public function showUserList(Request $request){
            $user = new User();
            $information = $user->showUserList();
            return Common::returnJsonResponse(1,'查找用户列表成功',$information);
        }





    public function getUserTaskLike(Request $request)
    {
        $realname = $request->input('realname');
        $client = new Client();
        $result = $client->getUserTaskLike($realname);
        if($result){
            $reultCode = 1;
            $resultMsg = '获取成功';
        }else{
            $reultCode = 0;
            $resultMsg = '获取失败';
        }
        $category = new Category();
        $userLike = $result->toArray();
        $data = $userLike[0]['like_image_class'];
        $category_ids = json_decode($data,true);
        //var_dump($category_ids);
        $category_nameL = [];
        foreach ($category_ids as $category_id)
        {
            $category_names = $category->getCategoryName($category_id)->toArray();
            $category_name = $category_names[0]['category_name'];
            $category_nameL[] = $category_name;
            //var_dump($category_name);
        }
       // var_dump($category_nameL);
        return Common::returnJsonResponse($reultCode,$resultMsg,$category_nameL);
    }




    public function upload(Request $request)
    {
    	$file = $request->file('zip');
        //$file = $request->file('zip');
        $filename =  $file->getClientOriginalName();
        $filenames = explode('.',$filename);
        $category_name = $filenames[0];
        $extend = $filenames[1];
        $zipname = $category_name.'.'.$extend;
        $file->move('Image/'.$category_name.'/',$zipname);
        $path = "Image/".$category_name."/";
        $zipfile = zip_open('Image/'.$category_name.$zipname);
        $bool = system('unzip '.$path.$zipname.' -d '.$path);
        system('rm -f '.$path.$filename);
        if($bool){
           return Common::returnJsonResponse(1,'上传成功',null);
        }else{
           return Common::returnJsonResponse(0,'上传失败',null);
        }
    }

}