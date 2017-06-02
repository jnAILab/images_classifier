<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Label extends Model{
    protected $table='label';  //指定表名

    protected $primaryKey = 'auto_id';  //指定主键

    protected $guarded = ['auto_id'];  //不可批量添加的字段

    /**
     *
     * @auther 张政茂
     *
     * 此方法用于储存用户标记的标签内容，前台需要传递用户id图片id
     * 标签id及标签内容
     *
     * @param
     *
     *关联创建任务表，用户id尾数值和图片id后三位作为表名，同时储存标签内容
     *
     */
    public function storeLabelContent($user_id,$label_id,$label_name,$image_id){

        //将标签内容进行MD5加密
        $name_md5 = md5($label_name);

        //使用模型的create方法新增数据(将标签内容储存在标签表)
        Label::create(
            ['label_id'=>$label_id,'label_name'=>$name_md5,'image_id'=>$image_id]
        );

        //获取user_id最后一位，image_id 后三位
        //substr('字符串'，获取前（后）几位数值)
        $task_name=substr($user_id,-1)."_".substr($image_id,-3)."_task";

        //若不存在此图片任务的表（A_BBB_task类型的表），则创建
        DB::select('create table if not exists images_classifier.'.$task_name.'(
				auto_id  INT(6) not null AUTO_INCREMENT,
				primary key (auto_id),
				user_id varchar(16) not null,
				image_id varchar(16) not null,
				user_assign_label MEDIUMTEXT,
				user_assign_label_id MEDIUMTEXT
				)engine innoDB');

        //将标签内容储存在任务表
        $bool = DB::table($task_name)
            ->insert(
                ['user_assign_label'=>$name_md5,'user_assign_label_id'=>$label_id,'user_id'=>$user_id,'image_id'=>$image_id]
            );

    }

    /**
     * @auther 张政茂
     *
     *
     * 获取已有的标签内容
     * 根据前台的用户id与图片id
     * 从数据库获取这个用户u对这个图片的标记内容并返回
     *
     * @param user_id ： 用户的id
     * @param image_id ：图片的id
     * @param label_id ：标签的id
     *
     * @return {
     *      "ResultCode":1,
     *      "ResultMsg":"获取标签",
     *      "Date":"储存标签时MD5加密后的标签"
     *}
     */
    public function getLabelContent($user_id,$image_id,$label_id)
    {

        //获取user_id最后一位，image_id 后三位
        //substr('字符串'，获取前（后）几位数值)
        $task_name=substr($user_id,-1)."_".substr($image_id,-3)."_task";

        $task = DB::table($task_name)
            ->where('user_id',$user_id)
            ->where('user_assign_label_id',$label_id)
            ->where('image_id',$image_id)
            ->get();

        //返回查询的标签内容
        return $task[0]->user_assign_label;

    }

    /**
     * @auther 张政茂
     *
     * 更新标签内容
     * 根据用户id和图片id修改label表和任务表中的标签内容
     *@param $request
     *
     *
     */
    public function updateLabelContent($user_id,$label_id,$image_id,$label_name)
    {

        //将标签内容进行MD5加密
        $name_md5 = md5($label_name);

        //更新标签内容
        Label::where('image_id',$image_id)
            ->where('label_id',$label_id)
            ->update(['label_name'=>$name_md5]);

        //获取user_id最后一位，image_id 后三位
        //substr('字符串'，获取前（后）几位数值)
        $task_name=substr($user_id,-1)."_".substr($image_id,-3)."_task";

        DB::table($task_name)
            ->where('user_assign_label_id',$label_id)
            ->where('user_id',$user_id)
            ->where('image_id',$image_id)
            ->update(['user_assign_label'=>$name_md5]);

    }

    /**
     * @auther 张政茂
     * @param $request
     *
     *删除标签
     * 根据用户id和图片id
     * 以及打算删除的标签内容从数据库删除记录
     *
     */

    public function deleteLabel($user_id,$image_id,$label_id,$label_name)
    {
        //将标签内容进行MD5加密
        $label_name_md5 = md5($label_name);

        //在数据库label表删除标签
        Label::where('label_name',$label_name_md5)
            ->where('image_id',$image_id)
            ->where('label_id',$label_id)
            ->delete();

        //获取user_id最后一位，image_id 后三位
        //substr('字符串'，获取前（后）几位数值)
        $task_name=substr($user_id,-1)."_".substr($image_id,-3)."_task";

        //在任务表中删除标签
        $result = DB::table($task_name)
            ->where('user_id',$user_id)
            ->where('image_id',$image_id)
            ->where('user_assign_label_id',$label_id)
            ->where('user_assign_label',$label_name_md5)
            ->delete();
        return ['date'=>$result];

    }
}

?>