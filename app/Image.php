<?php
namespace App;
use App\Client;
use App\Common;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Array_;

class Image extends Model{
    protected $table = 'image';  //指定表名
    protected $primaryKey = 'auto_id';  //指定主键
      //关闭自动添加时间
    protected $guarded = ['auto_id'];  //不可批量添加的字段（黑名单）

    /**
     * @author killer 2017年6月4日01:48:48
     * 根据用户的信息推送一个图片
     * @param User $user
     * @return bool or image_id
     */
    public function pushImage(User $user){
        if($user->status != 'client'){
            //检查一下被分配任务的用户身份是否在用户
            return false;
        }
        //判断当前用户是否已经被分配了任务（预分类的图片不算做已被分配的任务）
        $imageCheckResult = $this->checkImagesIdByTask($user->user_id);
        if($imageCheckResult === false){
            //用户未分配任何任务。
            //判断当前用户是否为新用户
            $userCheckResult = $this->checkUserImageInformation($user->user_id);
            if($userCheckResult === true){
                //当前用户未因注册用户，即无标记信息。则在所有的图片库里面随机抽取50张图片
                //（50张图片= 20张预分类图片+30张正式分类图片。若该用户注册时，预分类的图片已被分配完毕，则分配50张随机图片）
                return $this->randomGetImageIds($user->user_id);
            }else{
                //根据用户的喜好分配200张图片
                $image_id = json_decode($this->getImagesBySocket($user->user_id));
                //print_r($image_id);
                $randomImageIds = $this->randomGetImageIds($user->user_id);
                return array_merge($image_id,$randomImageIds);
            }
        }else{
            return $imageCheckResult;//返回图片ids
        }
        return null;
    }

    public function getImagesBySocket($user_id){
        $host = 'tcp://122.112.253.167:12308';
        $fp = stream_socket_client ( $host, $errno, $error, 20 );
        if(!$fp){
            echo "$error ($errno)";
        }else{
            fwrite ($fp,$user_id);
            while (!feof($fp)){
                //var_dump($fp);
                $image_ids = fgets($fp); #获取服务器返回的内容
            }
            fclose ($fp);
        }
        return $image_ids;
    }

    public function checkUserImageInformation($user_id){
        $result = DB::table('image_label')->select('auto_id')->where('users_added','like',"%$user_id%")->get();
        //return $result;
        if(count($result)>0){
            return false;
        }else{
            return true;
        }
    }


    /**
     * @author killer 2017年6月4日01:48:11
     * 检查用户被分配的任务中图片是否全部为未标记图片
     * @param $user_id
     * @return array
     */
    public function checkImagesIdByTask($user_id){
        //获取用户已被分配的任务
        $imagesId = $this->getAssignTask($user_id);
        if(count($imagesId)==0){
            return false;
        }
        $result = DB::table('image')->select('status')->whereIn('image_id',$imagesId)->get();
        //将所有查询到的status结果相加到一起，若大于0则图片中包含已被分类的图片，则认为该用户已经被分配了任务。
        $unfinished = false;
        foreach($result as $image){
            if($image->status == 0){
                $unfinished = true;
                break;
            }
        }
        //echo $flag;
        if(!$unfinished){
            //用户未被分配任务
            return false;
        }else{
            //已经被分配了任务。
            return $imagesId;
        }
    }

    function getAssignTask($user_id){
        $imagesId = array();
        $tables_name = Common::generateDatabaseNamesByClientId($user_id);
        //return $tables_name;
        foreach($tables_name as $table_name){
            $imagesIdInTable = DB::table($table_name)->select('image_id')->where('user_id','=',$user_id)->where('status','=',0)->get();
            foreach($imagesIdInTable as $image_id){
                $imagesId[] = $image_id->image_id;
            }
        }
        return $imagesId;
    }



    public function randomGetImageIds($user_id){
        //随机获取图片
        //首先获取当前用户已经被分配了的预分配图片的任务
        $imagesId = $this->getAssignTask($user_id);
        if(count($imagesId)>=50){
            //若当前用户已经被分配了超过50张图片，则直接将图片id集push出去
            return $imagesId;
        }else{
            $totalImageNumber = DB::table('image')->count();
            //否则计算需要补充的图片个数。
            $needImageNumber = 50-(int)(count($imagesId));
            //根据上面计算到的个数，随机获取图片
            $randomIndex=array();
            while(count($randomIndex)<$needImageNumber){
                $randomIndex[] = mt_rand(1,$totalImageNumber);
                $randomIndex = array_unique($randomIndex);
                $randomIndex = array_diff($randomIndex,$imagesId);
            }
            $randomImages = DB::table('image')->select('image_id')->whereIn('auto_id',$randomIndex)->get();
            foreach($randomImages as $image){
                $imagesId[] = $image->image_id;
            }
            return $imagesId;
        }

    }

    /**
     * 显示图片标记信息的函数
     * @param $id
     *
     * @return $info
     * */
    public function getImageMarkedInformation($id){
        $info = DB::table('image_label')
            ->leftJoin('label', 'image_label.label_id', '=', 'label.label_id')
            ->select('image_label.image_id', 'label.label_name',"image_label.label_id","image_label.like_number")
            ->where('image_id','=',$id)->where('image_label.is_del','=','0')->orderBy('image_label.like_number', 'asc')->limit(5)
            ->get();
        return $info;
    }

    /**
     * @author dain 2017.6.4 15:00
     * 操作数据库读取image_location
     * @param $image_ids
     * @return array
     */
    public function getImageLocationInImage($image_ids){
        $imageLocation = array();
        //dd($image_ids);
        foreach ($image_ids as $image_id){
            $image_locations = DB::table('image')
                ->where('image_id', $image_id)
                ->select('image_location')
                ->get();
//               dd($image_locations);
            foreach ($image_locations as $image_location){
                $imageLocation[] = $image_location->image_location;
            }
        }
        return $imageLocation;
    }
    public function getLabeledImageNumber(Request $request){
        $imageLabelObj = new Image_Label();
        $result = $imageLabelObj->all(); //获取全部的数据
        $allLabeledImageNumber = count($result);
        $result = $imageLabelObj->where('updated_at','>',date('Y-m-d H:i:s',strtotime("-7 day")))->get();//先获得一周内被操作过的图片列表。
        $labeledImageNumberForWeek = count($result);
        return Common::returnJsonResponse(1, 'query successful', array('allLabeledImageNumber'=>$allLabeledImageNumber,'labeledImageNumberForWeek'=>$labeledImageNumberForWeek));
    }

    //图片标记数统计图
    public function imageSignNumber(){
        $taskNames=Array();
        $tableNames = DB::select("select table_name from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = 'images_classifier' ");
        foreach($tableNames as $tableName){
            if(strstr($tableName->table_name,"task")){
                $taskNames[]=$tableName->table_name;
            }
        }

        date_default_timezone_set('PRC');
        $number_wk=date("w",time());
        if($number_wk==0){
            $number_wk=7;
        }
        $oneDay = 60*60*24;
        $theLastWeek = time()-$oneDay*$number_wk;

        $allTasksNumber=array();
        $allFinishTasksNumber=array();
        foreach($taskNames as $tableName){
            $allTasksNumber[]=DB::table($tableName)
                ->select('assign_time')
                ->get()
                ->toArray();
            $allFinishTasksNumber[]=DB::table($tableName)
                ->where('status',1)
                ->select('assign_time')
                ->get()
                ->toArray();
        }

        $total=self::getNumber($allTasksNumber);
        $completion=self::getNumber($allFinishTasksNumber);

        $data=[
            'timeAxis'=>['五周前','四周前','三周前','两周前','一周前','本周'],
            'total'=>$total,
            'completion'=>$completion
        ];

        return $data;
    }

    //给定格式化的时间，返回属于第几周
    public function week($time){
        $time=strtotime($time);
        date_default_timezone_set('PRC');
        $now=time();
        $number_wk=date("w",$now);
        if($number_wk==0){
            $number_wk=7;
        }
        $oneDay=60*60*24;
        $day=(strtotime(date('Y-m-d'))-strtotime(date('Y-m-d',$time)))/$oneDay;
        if($day<$number_wk){
            $week=0;
        }else{
            $week=1+floor(($day-$number_wk)/7);//floor去尾法取整数
        }
        return $week;
    }

    //给定两钟情况的总数，返回以六个周分组，格式化好的数据。
    public function getNumber($data){
        $one=0;
        $two=0;
        $three=0;
        $four=0;
        $five=0;
        $six=0;
        foreach($data as $oneTable){
            if($oneTable!=[]){
                foreach($oneTable as $oneTask){
                    if($oneTask!=[]){
                        $week = self::week($oneTask->assign_time);
                        if($week==0){
                            $one+=1;
                        }elseif($week==1){
                            $two+=1;
                        }elseif($week==2){
                            $three+=1;
                        }elseif($week==3){
                            $four+=1;
                        }elseif($week==4){
                            $five+=1;
                        }elseif($week==5){
                            $six+=1;
                        }
                    }
                }
            }
        }
        return [$six,$five,$four,$three,$two,$one];
    }

}
?>