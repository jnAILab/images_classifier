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
use Illuminate\Http\Request;

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
     *      "ResultCode": 1,
     *      "ResultMsg": 成功储存标签,
     *      "Date": null
     * }
     */
    public function storeLabelContent(Request $request)
    {
        $Label = new Label();

        $user_id = $request->input('user_id');
        $label_id = $request->input('label_id');
        $label_name = $request->input('label_name');
        $image_id = $request->input('image_id');

        $result = $Label->storeLabelContent($user_id,$label_id,$label_name,$image_id);

        return Common::returnJsonResponse(1,'成功储存标签',$result);

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
     *      "ResultCode": 1,
     *      "ResultMsg": "获取标签",
     *      "Date": "储存标签时MD5加密后的标签"
     * }
     *
     */
    public function getLabelContent(Request $request)
    {
        $Label = new Label();

        //将获取信息赋值给变量
        $user_id = $request->input('user_id');
        $image_id = $request->input('image_id');
        $label_id = $request->input('label_id');

        $result = $Label->getLabelContent($user_id,$image_id,$label_id);

        return Common::returnJsonResponse(1,'获取标签',$result);
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
     *      "ResultCode": 1,
     *      "ResultMsg": 成功更新标签,
     *      "Date": null
     * }
     */
    public function updateLabelContent(Request $request)
    {
        $Label = new Label();

        //将获取信息赋值给变量
        $user_id = $request->input('user_id');
        $image_id = $request->input('image_id');
        $label_id = $request->input('label_id');
        $label_name = $request->input('label_name');

        $result = $Label->updateLabelContent($user_id,$label_id,$image_id,$label_name);

        return Common::returnJsonResponse(1,'成功更新标签',$result);
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
     *      "ResultCode": 1,
     *      "ResultMsg": "删除标签",
     *      "Date": 1 or 0（1：一行已被删除;0:零行被删除）
     * }
     */
    public function deleteLabel(Request $request)
    {
        $Label = new Label();

        //将获取信息赋值给变量
        $user_id = $request->input('user_id');
        $image_id = $request->input('image_id');
        $label_id = $request->input('label_id');
        $label_name = $request->input('label_name');

        $result = $Label->deleteLabel($user_id,$image_id,$label_id,$label_name);

        return Common::returnJsonResponse(0,'删除标签',$result);
    }

}