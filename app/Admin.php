<?php
	namespace App;
	use Illuminate\Database\Eloquent\Model;
	
	class Admin extends Model{
		protected $table = 'admins';  //指定表名
			
		protected $primaryKey = 'auto_id';  //指定主键
		
		protected $guarded = ['auto_id'];  //不可批量添加的字段（黑名单）
			
		/**
		*
		*@author 范留山
		*增加管理员，user_id 根据user表自增id进行md5加密
		*
		*@param  $username 前台发送的用户名
		*@param  $password 前台发送的密码
		*@param  $employeeId 前台发送的工号
		*@param  $email 前台发送的邮箱
		*
		*@return  [
		*			'resultCode'=>0 或 1,
		*			'resultMsg' => '添加管理员成功' 或 '添加管理员失败'
		*			];
		*
		*/
		public function addAdministrator($username,$password,$email,$employeeId){
			
			//向user表插入信息
			$result_insert= User::insert([
				'name' => $username,
				'password' => md5($password),
				'email' => $email,
				'remember_token' => $username,
				'status' => 'admin',
			]);
			//将users表自增id加密后作为user_id插入users表和admin表
			$user_id=uniqid();
			$result_information = User::where('auto_id',$id)->update([
				'user_id'=>$user_id,
			]);
			$result_admin = Admin::create([
				'user_id'=>$user_id,
				'employee_id'=>$employeeId//插入管理员工号
			]);
			
			//判断信息是否插入成功
			if ($result_insert&&$result_information&&$result_admin){
				$resultCode=0;
				$resultMsg='添加管理员成功';
			}else{
				$resultCode=1;
				$resultMsg='添加管理员失败';
			}
			
			return json_encode([
				'resultCode'=>$resultCode,
				'resultMsg' => $resultMsg
			]);
		}
		
		 /*
		* @author 田荣鑫
		* 删除管理员（deleteAdministrators,可批量）
		* @param $user_id   前台传值到后台经过md5加密值，用来判断哪位管理员
		* @return  [
		* 			    'resultMsg' => '删除成功' 或 '删除失败'
		*           ]
		*/
		public function  deleteAdministrators($user_id)
		{
			//删除操作 数据库为更新is_del数值为1
			//$adminDelResult  admin表操作影响行数
			//$userDelResult    users表操作影响行数
			$adminDelResult = DB::table('admins')
				->where('user_id',$user_id)
				->update(['is_del'=>1]);
			$userDelResult = DB::table('users')
				->where('user_id',$user_id)
				->update(['is_del'=>1]);
			//操作结果判断
			if($adminDelResult>0  && $userDelResult>0){
				$resultMsg = '删除成功';
			}else{
				$resultMsg = '删除失败';
			}
			//结果返回
			return json_encode([
				'resultMsg' => $resultMsg
			]);
		}

		/*
		* @author 田荣鑫
		* 修改管理员密码  （允许其他管理员修改别人的密码？）
		* @param $user_id   前台传值到后台经过md5加密值，用来判断哪位管理员。
		* @param $newPassword   前台获取的新的管理员密码
		* @return  [
		* 			    'resultMsg' => '修改成功' 或 '修改失败'
		*           ]
		*
		*/
		public function alterAdminPsd($user_id,$newPassword)
		{
			//修改密码，基于user_id
			$result = DB::table('users')
				->where('user_id',$user_id)
				->update(['password'=>$newPassword]);

			//判断是否修改成功
			if($result>0){
				$resultMsg='修改成功';
			}else{
				$resultMsg='修改失败';
			}
			//结果返回
			return json_encode([
				'resultMsg' => $resultMsg
			]);
		}
	}
?>