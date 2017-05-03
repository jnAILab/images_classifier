<?php
/**
 * Created by PhpStorm.
 * User: n
 * Date: 2017/4/25 0025
 * Time: 19:50
 */

namespace App\Http\Controllers;


use App\Common;
use App\Admin;
use App\User;
use Illuminate\Http\Request;

class PersonController extends Controller
{
	/**
		*@author 聂恒奥
		*/
    public function changePassword(Request $request){
        $json = Common::changePassword($request);
        return $json;
    }
    public function increaseUserPoints(Request $request){
        $json = User::increaseUserPoints($request);
        return $json;
    }
    public function updatePersonInformation(Request $request){
        $json = Common::updatePersonInformation($request);
        return $json;
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
			$result = $alterPsd->alterAdministratorsPsd($user_id,$newPssword);
			return $result;
			}
		}
}