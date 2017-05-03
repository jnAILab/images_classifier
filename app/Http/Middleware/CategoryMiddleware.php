<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-5-2
 * Time: 下午 9:42
 */
namespace App\Http\Middleware;
use App\Category;
use Closure;
use function PHPSTORM_META\type;

/**
 * Class CategoryMiddleware
 * @package App\Http\Middleware
 * @author 葛操
 * 添加图片类型 中间件
 */

class CategoryMiddleware
{
    public function handle($request,Closure $next)
    {
        $ids = $request->input('ids');
        $names = $request->input('names');
        $locations = $request->input('locations');

        $i = 0;
        $flag =1;
        foreach ($ids as $id) {
            $tableId = Category::where('category_id',$id)->get();
            $tableName = Category::where('category_name',$names[$i])->get();
            $tableLocation = Category::where('category_location',$locations[$i])->get();
            echo $tableLocation;
            echo $tableName;
            echo $tableId;
            if($tableId)
            {
                echo '1231';
            }
            echo gettype($tableId);

            if ($tableId&&$tableName&&$tableLocation) {
                echo '第'.($i+1).'条数据重复，请检查！';
                exit();
           }
            $i++;
        }
        return $next($request);
    }
}







