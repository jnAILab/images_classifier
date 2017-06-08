<?php
/**
 * Created by PhpStorm.
 * User: n
 * Date: 2017/4/25 0025
 * Time: 19:50
 */

namespace App\Http\Controllers;

use APP\Client;
use App\Common;
use App\Admin;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        
        
        
        
        
        
		public function getPersonInformation(Request $request){
            $user_id = $request->input('user_id');
            $client = new Client();
            $personInformation = $client->getPerInformationToShow($user_id);
            return Common::returnJsonResponse($personInformation);
        }
}