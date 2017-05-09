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
        $user_id = $request->input('user_id');
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
        $user_ids = $request->input('user_ids');
        $user_points = $request->input('user_points');
        $user = new User();
        $ResultCode = $user->increaseUserPoints($user_ids,$user_points);
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

        $ResultCode = $Common->updatePersonInformation($all);
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
		*添加管理员
		*/
		public function addAdmin(Request $request){
			$username = $request->input('sendUsername');
			$password = $request->input('sendPassword');
			$employeeId = $request->input('sendEmployeeId');
			$email = $request->input('sendEmail');
			
			$addAdmin=new Admin();
			$result = $addAdmin->addAdministrator($username,$password,$email,$employeeId);
			return $result;
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
			return $result;
		}

		/* *
		* auther 田荣鑫
		* 获取管理员列表，获取的管理员名字 为真实姓名
		*/
		public function getAdministratorList()
		{
			$getAdminList =DB::table('admins')
				->where('is_del',0)
				->select('realname')
				->get();
			return json_decode($getAdminList);
		}

		/**
		*@author 田荣鑫
		* 更改管理员密码
		*/
		public function alterAdminPsd(Request $request)
		{
			$user_id = $request->input('user_id');
			$newPssword = $request->input('newPassword');
			$alterPsd = new Admin();
			$result = $alterPsd->alterAdminPsd($user_id,$newPssword);
			return $result;
		}
		public function getPersonInformation(Request $request){
            $user_id = $request->input('user_id');
            $client = new Client();
            $personInformation = $client->getPerInformationToShow($user_id);
            return Common::returnJsonResponse($personInformation);
        }
}