<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-5-9
 * Time: 上午 12:31
 */
namespace App\Http\Middleware;
use App\Category;
use App\Common;
use Closure;


/**
 * @auther 葛操
 * 更新图片类别列表中间件
 */

class updateCategoryMiddleware
{
    public function handle($request,Closure $next)
    {
        $ids = $request->input('ids');
        $names = $request->input('names');
        $i = 0;
        foreach ($ids as $id) {
            $tableId = Category::where('category_id',$id)->first();
            $tableName = Category::where('category_name',$names[$i])->first();

            if (!$tableId || $tableName) {
                return Common::returnJsonResponse($resultCode = 0,$resultMsg = '失败',$data = null);
            }
            $i++;
        }
        return $next($request);
    }
}