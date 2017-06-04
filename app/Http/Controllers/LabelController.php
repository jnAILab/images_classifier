<?php
/**
 * Created by PhpStorm.
 * User: ZZM
 * Date: 2017/4/27
 * Time: 15:17
 */

namespace App\Http\Controllers;

use App\Common;
use App\Label;
use App\Image_Label;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabelController extends Controller
{
    /**
     *
     * @auther 张政茂
     *
     * 此方法用于储存用户标记的标签内容，前台需要传递用户id图片id
     * 标签id及标签内容
     *
     * @param user_id : 用户的id
     * @param label_id : 标签的id
     * @param label_name : 标签的内容
     * @image_id  被标记图片的id
     *
     *关联创建任务表，用户id尾数值和图片id后三位作为表名，同时储存标签内容
     *
     * @return {
     *      "Result": 1/0（1成功）
     *      "remind: 添加成功,
     *      "Date": null
     * }
     */

    public function storeLabelContent(Request $request)
    {

        $user_id = $request->input('user_id');
        $label_name = $request->input('label_name');
        $image_id = $request->input('image_id');


        //获取label_id这一字段的所有值
        $label_ids = DB::table('label')->select('label_id')->get()->toArray();

        //为label_id赋值
        //$label_id ;
        $is_del = 1;

        //查询$label_ids数组中最大的id，若不为空则获得后加一，否则label_id=1
        if($label_ids)
        {
            //echo $label_id;
            $pos=array_search(max($label_ids),$label_ids);
            $label_id = $label_ids[$pos]->label_id;

            $label_id++;

        }else{
            $label_id = 1;

        }
        //echo $label_id;

        $Label = new Label();

        //向label模型中传递参数，向storelabelcontent中传递
        $result0 = $Label->storeLabelContent($user_id,$label_id,$label_name,$image_id,$is_del);


        $image_label = new Image_Label();

        //向image_label中的addId传递参数
        $result = $image_label->addId($image_id,$label_id,$is_del);

        //echo $result;
        //如果都为真则返回添加成功，否选择返回添加失败
        if($result0&&$result)
        {
            $remind = '添加成功';
        }else{
            $remind = '添加失败';
        }

        return Common::returnJsonResponse($result,$remind,$data = null);

    }

    /**
     *@auther 张政茂
     *
     * 获取已有的标签内容
     * 根据前台的用户id与图片id
     * 从数据库获取这个用户u对这个图片的标记内容并返回
     *
     * @param user_id : 用户的id
     * @param image_id : 图片的id
     * @param label_id : 标签的id
     *
     * @return{
     *      "Result": 1,
     *      "remind": "查询成功",
     *      "Date": "标签内容"
     * }
     */
    public function getLabelContent(Request $request)
    {
        $Label = new Label();

        //将获取信息赋值给变量
        $user_id = $request->input('user_id');
        $image_id = $request->input('image_id');
        //向label模型中传递数据，返回一个数组
        $result0 = $Label->getLabelContent($user_id,$image_id);

        //将数组中的值赋给$result和$data1
        $result = $result0[0];
        $data1 = $result0[1];
        if($result0[0])
        {
            $remind = '查询成功';
        }else{
            $remind = '查询失败';
        }
        //成功是data返回查询到的标签内容，否则data返回failure
        return Common::returnJsonResponse($result,$remind,$data = $data1);
    }

    /**
     * @auther 张政茂
     *
     * 更新标签内容
     * 根据用户id和图片id修改label表和任务表中的标签内容
     *
     * @param user_id : 用户id
     * @param label_id : 标签的id
     * @param image_id : 图片的id
     * @param label_name : 将要更新的新标签内容
     *
     * @return {
     *      "Result": 1,
     *      "remind": 成功更新标签,
     *      "Date": null
     * }
     */
    public function updateLabelContent(Request $request)
    {
        $Label = new Label();

        //将获取信息赋值给变量
        $user_id = $request->input('user_id');
        $image_id = $request->input('image_id');
        $label_name = $request->input('label_name');
        //将数据传递给label模型
        $result = $Label->updateLabelContent($user_id,$image_id,$label_name);

        if($result)
        {
            $remind = '更新成功';
        }else{
            $remind = '更新失败';
        }
        //返回结果
        return Common::returnJsonResponse($result,$remind,$data = null);
    }

    /**
     *@auther 张政茂
     *
     *删除标签
     * 根据用户id和图片id
     * 以及打算删除的标签内容从数据库删除记录
     *
     * @param user_id : 用户的id
     * @param image_id : 图片的id
     * @param label_name : 将要删除的标签的内容
     *
     * @return {
     *      "Result": 1,
     *      "remind": "删除标签",
     *      "Date": 1 or 0（1：已被删除;0:未被删除）
     * }
     */
    public function deleteLabel(Request $request)
    {
        $Label = new Label();

        //将获取信息赋值给变量
        $user_id = $request->input('user_id');
        $image_id = $request->input('image_id');
        $label_name = $request->input('label_name');


        $result = $Label->deleteLabel($user_id,$image_id,$label_name);

        if($result)
        {
            $remind = '软删除成功';
        }else{
            $remind = '软删除失败';
        }
        //返回结果
        return Common::returnJsonResponse($result,$remind,$data = null);
    }

}