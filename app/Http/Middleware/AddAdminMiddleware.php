<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use Illuminate\Contracts\Auth\Factory as Auth;

class AddAdminMiddleware
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * 管理员注册中间件
     *
     *@global  \Illuminate\Http\Request  $request
     *@param  \Closure  $next
     *@param  string|null  $guard
     *@param  sendUsername 前台发送的 用户名
     *@param  sendPassword 前台发送的 密码
     *@param  sendEmployeeId 前台发送的 工号
     *@param  sendPasswordAgain 前台发送的 第二次输入的密码
     *@
     *@return 通过验证返回$next($request)，
     *		否则返回json数据  ['resultCode'=>'0','remind'=>'xxx']
     *		 (判断是否验证成功 1 成功、0 失败，XXX 返回的提醒内容)
     */
	public function handle($request, Closure $next){
		
		//接收数据
		$password=$request->input('sendPassword');
		$email=$request->input('sendEmail');
		
		//查找用户名，用于判断用户名是否存在
		$email_result = User::select('email')
			->where('email',$email)
			->first();
		//输入判断
		if($password==''){
			return ['resultCode'=>'0','remind' => '请输入密码'];
		}elseif(strlen($password)<6){
			return ['resultCode' => '0','remind' => '密码长度不能小于6个字符'];
		}elseif($email==''){
			return ['resultCode' => '0','remind' =>"请输入邮箱"];
		}elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			return ['resultCode' => '0','remind' =>"输入邮箱不合法"];
		}elseif($email_result!=null){
			return ['resultCode' => '0', "remind" => '邮箱已经被注册'];
		}else{
			return $next($request);
		}
		
	}
}
