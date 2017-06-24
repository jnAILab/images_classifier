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
use Tymon\JWTAuth\Facades\JWTAuth;

class LabelController extends Controller
{

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
     *      "Result": 1/0,
     *      "remind": "查询成功"/"查询失败",
     *      "Date": "标签内容"
     * }
     */
    public function getLabelContent(Request $request)
    {
        $Label = new Label();

        //将获取信息赋值给变量
        $user_id = JWTAuth::parseToken()->authenticate()->user_id;
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
        return Common::returnJsonResponse($result0[0],$remind,$result0[1]);

    }

    /**
     * @auther 张政茂
     *
     * 更新标签内容
     * 根据用户id和图片id修改label表和任务表中的标签内容
     *
     * @param user_id : 用户id
     * @param image_id : 图片的id
     * @param label_name : 将要更新的新标签内容
     *
     * @return {
     *      "Result": 1/0,
     *      "remind": ‘更新成功’/‘更新失败’
     *      "Date": null
     * }
     */
    public function updateLabelContent(Request $request)
    {
        $Label = new Label();

        //将获取信息赋值给变量
        $user_id = JWTAuth::parseToken()->authenticate()->user_id;
        $image_id = $request->input('image_id');
        $label_name = $request->input('label_name_new');
        $label_id = md5($label_name);
        $task_id = $request->input('task_id');
        $label_name_old = $request->input('label_name_old');
        $label_id_old = md5($label_name_old);
        //将数据传递给label模型
        $result = $Label->updateLabelContent($user_id,$image_id,$label_name,$label_id,$task_id,$label_id_old,$label_name_old);

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
     *      "Result": 1/,
     *      "remind": "软删除成功"/"软删除失败",
     *      "Date": null
     * }
     */
    public function deleteLabel(Request $request)
    {
        $Label = new Label();

        //将获取信息赋值给变量
        $user_id = JWTAuth::parseToken()->authenticate()->user_id;
        $image_id = $request->input('image_id');
        $label_name = $request->input('label_name');
        $label_id = md5($label_name);

        $result = $Label->deleteLabel($image_id,$label_id);

        if($result)
        {
            $remind = '软删除成功';
        }else{
            $remind = '软删除失败';
        }
        //返回结果
        return Common::returnJsonResponse($result,$remind,$data = null);
    }


    /**
     *
     * @auther killer
     *
     * 此方法用于储存用户标记的标签内容，前台需要传递用户id图片id
     * 标签id及标签内容
     *
     * @param token
     * @param label_id : 标签的id
     * @param label_name : 标签的内容
     * @param image_id  被标记图片的id
     *@param task_id  任务id
     *
     *关联创建任务表，用户id尾数值和图片id后三位作为表名，同时储存标签内容
     *
     * @return {
     *      "Result": 1/0（1成功）
     *      "remind: add successful,
     *      "Date": null
     * }
     */
    public function recordLabelWithImage(Request $request){
        //获取参数
        $image_id = $request->input('image_id');
        $label_name = $request->input('label_name');
        $task_id = $request->input('task_id');
        $label_id = md5($label_name);
        $user_id  = JWTAuth::parseToken()->authenticate()->user_id;
        //信息不完整
        if(is_null($image_id)||is_null($label_name)||is_null($label_id)||is_null($user_id)||is_null($task_id)){
            return Common::returnJsonResponse(0,'data is not complete',$data = null);
        }
        $Label = new Label();
        //向label模型中传递参数，向storelabelcontent中传递
        $result0 = $Label->storeLabelContent($user_id,$label_id,$label_name,$image_id,$task_id);
        //var_dump($result0);
        //return;
        if(!$result0){
            return Common::returnJsonResponse(0,'failed to add',$data = null);
        }
        $image_label = new Image_Label();
        //向image_label中的addId传递参数
        $result1 = $image_label->addItem($image_id,$label_id,$user_id);
        //var_dump($result0);
        //echo $result;
        if(!$result1){
            return Common::returnJsonResponse(0,'failed to add',$data = null);
        }
        return Common::returnJsonResponse(1,'add successfully',$data = null);
    }

    public function likeLabelWithImage(Request $request){
        $image_id = $request->input('image_id');
        $label_id = $request->input('label_id');
        //信息不完整
        if(is_null($image_id)||is_null($label_id)){
            return Common::returnJsonResponse(0,'data is not complete',$data = null);
        }
        return $this->updateLikeNumber($image_id,$label_id,$like = true);
    }
    public function oppositeLabelWithImage(Request $request){
        $image_id = $request->input('image_id');
        $label_id = $request->input('label_id');
        //信息不完整
        if(is_null($image_id)||is_null($label_id)){
            return Common::returnJsonResponse(0,'data is not complete',$data = null);
        }
        return $this->updateLikeNumber($image_id,$label_id,$like = false);
    }
    function updateLikeNumber($image_id,$label_id,$like = true){

        $image_label = new Image_Label();
        //更新标签被点赞的个数
        $result = $image_label->updateLikeNumber($image_id,$label_id,$like);
        if(!$result){
            return Common::returnJsonResponse(0,'failed to change like number ',$data = null);
        }
        //更新每个用户下面的信息
        $result = $image_label->updateLikeNumberPerUser($image_id,$label_id,$like);
        if(!$result){
            return Common::returnJsonResponse(0,'failed to change like number ',$data = null);
        }
        return Common::returnJsonResponse(1,'change like number successfully',$data = null);
    }

    /**
     * @author 范留山 2017-6-11
     * 查看 图片id，对该图片进行过标注的用户（数组返回用户名），改图片已经有的标签（数组返回标签名）
     * @param image_id 图片id 数组
     */
    public function seeExport(Request $request){
        $label = new Label();
        $results = $label->imageExecl();
        return Common::returnJsonResponse(1,'查找成功',$results);
    }
    /**
     * @author 范留山 2017-6-4
     *  下载excel 图片id，对该图片进行过标注的用户（数组返回用户名），改图片已经有的标签（数组返回标签名）
     * @param image_id :图片id 数组
     */
    public function imageExecl (Request $request)
    {
        $label = new Label();
        $results = $label->imageExecl();
        set_time_limit ( 0 );

        //处理成二维数组，以便储存为excel   [[图片id1，标签名数组1，用户名数组1],[图片id2，标签名数组2，用户名数组2],……]
        $allData = array();
        foreach($results as $imageId=>$result) {
            $data=array();
            $data[] = $imageId;
            $data[] = json_encode($result["label_name_list"],JSON_UNESCAPED_UNICODE);
            $data[] = json_encode($result["user_id_list"],JSON_UNESCAPED_UNICODE);
            $allData[]=$data;
        }

        //第一行的名字
        $ht = array (
            '图片id',
            '标签名',
            '用户名',
        );

        $dataArr [0] = $ht;
        $dataArr = array_merge ( $dataArr, $allData );
        $this->outExcel ( $dataArr, 'image_label');//后面的参数改名字

    }
    //下载excel文件的方法
    function outExcel($dataArr, $fileName = '', $sheet = false) {
        require_once  '../vendor/download-xlsx.php';
        export_csv ( $dataArr, $fileName, $sheet );

        unset ( $sheet );
        unset ( $dataArr );
    }
}