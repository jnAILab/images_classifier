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
     * 此方法用于储存用户标记的标签内容,前台需要传递用户id图片id
     * 标签id及标签内容
     *
     * @param
     *
     *关联创建任务表,用户id尾数值和图片id后三位作为表名,同时储存标签内容
     *
     */
    public function storeLabelContent($user_id,$label_id,$label_name,$image_id,$task_id)
    {

        //使用模型的create方法新增数据(将标签内容储存在标签表)
        $labelResult = Label::select('auto_id')->where('label_id','=',$label_id)->get();
        $result = $labelResult->toArray();
        if(count($result) == 0){//如果当前的便签不存在则添加新的标签到数据库
            Label::create(
                [
                    'label_id'=>$label_id,
                    'label_name'=>$label_name,
                ]
            );
        }
        $task_table_name = Common::generateDatabaseNamesByClientIdAndImageId($user_id,$image_id);
        Common::checkDatabaseByTableName($task_table_name);
        //将标签内容储存在任务表
        $result  = DB::table($task_table_name)->where('task_id','=',$task_id)->first();
        $user_assign_label = $result->user_assign_label;
        $user_assign_label_id = $result->user_assign_label_id;
        //return $result;
        //获取数组数据
        if(is_null($user_assign_label)&&is_null($user_assign_label_id)){
            $user_assign_label = array();
            $user_assign_label_id = array();
        }else{
            $user_assign_label = json_decode($user_assign_label,true);
            $user_assign_label_id = json_decode($user_assign_label_id,true);
        }

        //更新数据
        if(!isset($user_assign_label[$label_name])){
            $user_assign_label[$label_name] = 1;
        }
        if(!isset($user_assign_label_id[$label_id])){
            $user_assign_label_id[$label_id] = 1;
        }

        DB::table($task_table_name)->where('task_id','=',$task_id)
            ->update(
                [
                    'user_assign_label'=>json_encode($user_assign_label),
                    'user_assign_label_id'=>json_encode($user_assign_label_id),
                ]
            );
        if($result){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @auther 张政茂
     *
     *
     * 获取已有的标签内容
     * 根据前台的用户id与图片id
     * 从数据库获取这个用户u对这个图片的标记内容并返回
     *
     * @param user_id ; 用户的id
     * @param image_id ;图片的id
     * @param label_id ;标签的id
     *
     * @return {
     *      "ResultCode":1,
     *      "ResultMsg":"获取标签",
     *      "Date":"储存标签时MD5加密后的标签"
     *  }
     */
    public function getLabelContent($user_id,$image_id)
    {

        //获取user_id最后一位，image_id 后三位
        //substr('字符串'，获取前（后）几位数值)
        $task_name=substr($user_id,-1)."_".substr($image_id,-3)."_task";

        //获取图片任务表中某图片的label_id
        $result0 = DB::table($task_name)
            ->where('user_id',$user_id)
            ->where('image_id',$image_id)
            ->get();

        //dd($result0);
        //将得到的id赋值给$label_id
        $label_id = $result0[0]->user_assign_label_id;


        $result1 = DB::table('label')
            ->where('label_id',$label_id)
            ->where('image_id',$image_id)
            ->get();

        $judge = $result1[0]->is_del;

        if($judge==0)
        {
            //根据图片任务表查询更加准确
            $result = DB::table($task_name)
                ->where('user_id',$user_id)
                ->where('image_id',$image_id)
                ->get();
            //定义一个空数组
            $task = [];
            if($result)
            {
                //为数组赋值
                $task[0] = 1;
                $task[1] = $result[0]->user_assign_label;
                return $task;
            }else{
                $task[0] = 0;
                $task[1] = null;
                return $task;
            }
        }else{
            //定义一个空数组
            $task = [];
            $task[0] = 0;
            $task[1] = null;
            return $task;
        }

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
    public function updateLabelContent($user_id,$image_id,$label_name)
    {
        //获取user_id最后一位，image_id 后三位
        //substr('字符串'，获取前（后）几位数值)
        $task_name=substr($user_id,-1)."_".substr($image_id,-3)."_task";

        //获取图片任务表中某图片的label_id
        $result0 = DB::table($task_name)
            ->where('user_id',$user_id)
            ->where('image_id',$image_id)
            ->get();

        //将得到的id赋值给$label_id
        $label_id = $result0[0]->user_assign_label_id;

        DB::table($task_name)
            ->where('user_id',$user_id)
            ->where('image_id',$image_id)
            ->update(['user_assign_label'=>$label_name]);

        //更新label表中的label_name
        $result = Label::where('image_id',$image_id)
            ->where('label_id',$label_id)
            ->update(['label_name'=>$label_name]);

        //若为真则返回1，否则返回零
        if($result)
        {
            return 1;
        }else{
            return 0;
        }

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

    public function deleteLabel($user_id,$image_id,$label_name)
    {

        //获取user_id最后一位，image_id 后三位
        //substr('字符串'，获取前（后）几位数值)
        $task_name=substr($user_id,-1)."_".substr($image_id,-3)."_task";

        //获取图片任务表中某图片的label_id
        $result0 = DB::table($task_name)
            ->where('user_id',$user_id)
            ->where('image_id',$image_id)
            ->get()
            ->toArray();

        $is_del = 1;

        //将得到的id赋值给$label_id
        $label_id = $result0[0]->user_assign_label_id;

        DB::table('image_label')
            ->where('image_id',$image_id)
            ->where('label_id',$label_id)
            ->update(['is_del'=>$is_del]);

        //在数据库label表删除标签
        $result = Label::where('label_name',$label_name)
            ->where('image_id',$image_id)
            ->where('label_id',$label_id)
            ->update(['is_del'=>$is_del]);

        //若为真则返回1，否则返回零
        if($result)
        {
            return 1;
        }else{
            return 0;
        }
    }
    /**
     * @author 范留山 2017-6-4
     * 将图片id 和点赞前三个的标签提取出来，形成excel表格，第一列是image_id,第二列标签，第三列标签id……
     * @param image_id
     *
     */
    public function imageExecl($imageIds){
        $data=array();
        foreach($imageIds as $imageId){
            $data[$imageId] = Label::join("image_label","image_label.label_id","label.label_id")
                ->select("label.label_name","label.label_id","image_label.like_number")
                ->where("image_label.image_id",$imageId)
                ->orderBy('image_label.like_number', 'DESC')
                ->take(3)
                ->get();
        }
        return $data;
    }
}

?>