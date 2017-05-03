<?php

namespace App\Http\Middleware;

use Closure;
use App\Image;
use Illuminate\Contracts\Auth\Factory as Auth;

class CreateOneTaskMiddleware
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
    	*author 范留山
     * 创建一个任务中间件
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *@
     *@return 通过验证返回$next($request)，否则返回json数据  ['resultCode'=>'0','remind'=>'xxx'] (判断是否验证成功 1成功、0失败，返回的内容)
     *@
     *@todo 补充验证是否能通过的条件
     *
     */
	public function handle($request, Closure $next){
		$userId=$request->input('sendUserId');
		$categoryId=$request->input('sendCategoryId');
		
		$images = Image::select('image_id')
			->where('category_id',$categoryId)
			->first();
		if($images!=null){
			return $next($request);
		}else{
			return ['resultCode' => '0' , 'remind' => '该类型暂时没有任务，请选择其他类型'];
		}
		
	}
}
