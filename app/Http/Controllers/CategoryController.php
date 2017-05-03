<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-4-25
 * Time: 下午 9:07
 */
namespace App\Http\Controllers;

use App\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    //显示图片类别的列表
    public function getCategoryList()
    {
        $categorys = Category::get();
        return json_encode($categorys);
    }

    //增加图片类别
    public function storegetCategories(Request $request)
    {
        Category::storegetCategories($request);
    }
    //修改图片类别的名称
    public function updateCategoryNames(Request $request)
    {
        Category::updateCategoryNames($request);
    }
    //删除图片类别
    public function deleteCategories(Request $request)
    {
        Category::deleteCategories($request);
    }
}
