<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-4-25
 * Time: 下午 9:07
 */
namespace App\Http\Controllers;

use App\Category;
use App\Common;
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
        $category = new Category();
        $data = $category->getCategoryList();
        if($data){
            $resultCode = 1;
            $resultMsg = '成功';
        }else{
            $resultCode = 0;
            $resultMsg = '失败';
        }
        return Common::returnJsonResponse($resultCode,$resultMsg,$data);
    }


    //增加图片类别
    public function storegetCategories(Request $request)
    {
        $names = $request->input('newCategoryName');
        $category = new Category();
        $resultCode = $category->storegetCategories($names);
        if($resultCode == 1){
            $resultMsg = '成功';
        }else{
            $resultMsg = '失败';
        }
        return Common::returnJsonResponse($resultCode,$resultMsg,$data = null);
    }


    //修改图片类别的名称
    public function updateCategoryNames(Request $request)
    {
        $category_ids = $request->input('category_id');
        $category_names = $request->input('category_names');
        $category = new Category();
        $resultCode = $category->updateCategoryNames($category_ids,$category_names);
        if($resultCode){
            $resultMsg = '修改成功';
        }else{
            $resultMsg = '修改失败';
        }

        return Common::returnJsonResponse($resultCode,$resultMsg,$data = null);
    }


    //删除图片类别
    public function deleteCategories(Request $request)
    {
        $category_id = $request->input('category_id');
        $category = new Category();
        $resultCode = $category->deleteCategories($category_id);
        if($resultCode == 1){
            $resultMsg = '成功';
        }else{
            $resultMsg = '失败';
        }
        return Common::returnJsonResponse($resultCode,$resultMsg,$data = null);
    }
}
