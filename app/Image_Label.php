<?php
/**
 * Created by PhpStorm.
 * User: ZZM
 * Date: 2017/6/3
 * Time: 19:09
 */

namespace App;
use App\Label;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Image_Label extends Model
{
    protected $table = 'image_label';

    protected $primaryKey = 'auto_id';

    protected $guarded = ['auto_id'];
    /**
     * @author killer 2017年6月4日 20:30:01
     * 向image_label中储存image_id和label_id信息
     * @param $image_id
     * @param $label_id
     * @param $user_id
     * @return bool
     */
    //向image_label中储存image_id和label_id
    public function addItem($image_id,$label_id,$user_id)
    {
        $result = $this->whereRaw('image_id = ? and label_id = ?',[$image_id,$label_id])->first();
        //添加信息该条信息
        if(count($result) == 0){//不存在
            $remind = Image_Label::create(
                [
                    'image_id'=>$image_id,
                    'label_id'=>$label_id,
                    'users_added'=>json_endcode(array($user_id))
                ]
            );
        }else{
            $users_added = json_decode($result->users_added,true);
            $users_added[] = $user_id;
            $users_added = array_unique($users_added);
            $remind = $this->whereRaw('image_id = ? and label_id = ?',[$image_id,$label_id])
                ->update(['users_added'=>json_encode($users_added)]);
        }
        //判断一下是否添加成功
        if($remind){
            return true;
        }else{
            return false;
        }
    }
    public function updateLikeNumber($image_id,$label_id,$like = true){
        $result = $this->whereRaw('image_id = ? and label_id = ?',[$image_id,$label_id])->first();
        if($like){
            $like_number = (int)($result->like_number)+1;
        }else{
            $like_number = (int)($result->like_number)-1;
        }
        $updatedResult = $this->whereRaw('image_id = ? and label_id = ?',[$image_id,$label_id])
            ->update(['like_number'=>$like_number]);
        if($updatedResult){
            return true;
        }else{
            return false;
        }
    }
    public function updateLikeNumberPerUser($image_id,$label_id,$like = true){
        DB::beginTransaction();
        $result = $this->whereRaw('image_id = ? and label_id = ?',[$image_id,$label_id])->first();
        $user_ids = json_decode($result->users_added,true);
        $label = Label::select('label_name')->where('label_id','=',$label_id)->first();
        $label_name = $label->label_name;
        foreach($user_ids as $user_id){
            $table_name = Common::generateDatabaseNamesByClientIdAndImageId($user_id,$image_id);
            $task = DB::table($table_name)->whereRaw('image_id = ? and user_id = ?',[$image_id,$user_id])->first();
            $user_assign_label = json_decode($task->user_assign_label,true);
            $user_assign_label_id = json_decode($task->user_assign_label_id,true);
            //更新数据
            if($like){
                $user_assign_label[$label_name]+=1;
                $user_assign_label_id[$label_id]+=1;
            }else{
                $user_assign_label[$label_name]-=1;
                $user_assign_label_id[$label_id]-=1;
            }
            $updatedResult = DB::table($table_name)->whereRaw('image_id = ? and user_id = ?',[$image_id,$user_id])
                ->update(['user_assign_label'=>json_encode($user_assign_label),'user_assign_label_id'=>json_encode($user_assign_label_id)]);
            if(!$updatedResult){
                DB::rollback();//事务回滚
                return false;
            }
        }
        DB::commit();
        return true;
    }
}

