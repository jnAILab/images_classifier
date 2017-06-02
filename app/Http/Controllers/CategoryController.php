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
        $ids = $request->input('ids');
        $names = $request->input('names');
        $locations = $request->input('locations');
        $is_dels = $request->input('is_dels');
        $category = new Category();
        $resultCode = $category->storegetCategories($ids,$names,$locations,$is_dels);
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
        $ids = $request->input('ids');
        $names = $request->input('names');
        $category = new Category();
        $resultCode = $category->updateCategoryNames($ids,$names);
        if($resultCode == 1){
            $resultMsg = '成功';
        }else{
            $resultMsg = '失败';
        }
        return Common::returnJsonResponse($resultCode,$resultMsg,$data = null);
    }


    //删除图片类别
    public function deleteCategories(Request $request)
    {
        $ids = $request->input('ids');
        $category = new Category();
        $resultCode = $category->deleteCategories($ids);
        if($resultCode == 1){
            $resultMsg = '成功';
        }else{
            $resultMsg = '失败';
        }
        return Common::returnJsonResponse($resultCode,$resultMsg,$data = null);
    }
}
