<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-5-2
 * Time: 下午 9:42
 */
namespace App\Http\Middleware;
use App\Category;

use App\Common;
use Closure;
use function PHPSTORM_META\type;

/**
 * Class CategoryMiddleware
 * @package App\Http\Middleware
 * author 葛操
 * 添加图片类型中间件
 */

class storeCategoryMiddleware
{
    public function handle($request,Closure $next)
    {
        $names = $request->input('newCategoryName');
        foreach ($names as $name) {

            $tableName = Category::where('category_name',$name)->first();
            if ($tableName) {
                return Common::returnJsonResponse(0,'数据重复',null);
            }
        }
        return $next($request);
    }
}







