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
        $ids = $request->input('ids');
        $names = $request->input('names');
        $locations = $request->input('locations');
        $i = 0;
        foreach ($ids as $id) {
            $tableId = Category::where('category_id',$id)->first();
            $tableName = Category::where('category_name',$names[$i])->first();
            $tableLocation = Category::where('category_location',$locations[$i])->first();
            if ($tableId&&$tableName&&$tableLocation||$tableLocation) {
                //echo '第'.($i+1).'条数据重复，请检查！';
                return Common::returnJsonResponse($resultCode = 0,$resultMsg = '失败',$data = null);
           }
            $i++;
        }
        return $next($request);
    }
}







