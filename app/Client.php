<?php
/**
 * Created by PhpStorm.
 * User: n
 * Date: 2017/4/26 0026
 * Time: 15:28
 */

namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Client extends Model
{
   protected $primaryKey = 'user_id';

    public $timestamps = false;

    public function getUserTaskLike($user_id)
    {
        $result = $this->select('like_image_class')->where('user_id',$user_id)->get();
        return $result;
    }

    public function getPerInformationToShow($user_id){

        $result = DB::table('users')
            ->join('clients','clients.user_id','users.user_id')
            ->select('name','realname','sex','idcarnumber','email')
            ->where('users.user_id',$user_id)
            ->first();
        return $result;
    }
    
    public function registerClient($realname,$idcarNumber,$sex,$user_id){
        $result = $this->where('user_id','=',$user_id)->get();
        if(count($result)!=0){
            return false;
        }else{
            $this->insert([
                'user_id'=>$user_id,
                'realname'=>$realname,
                'idcarnumber' => $idcarNumber,
                'sex'=>$sex,
                'updated_at' => date('Y-m-d H:i:s'),
                'created_at' =>  date('Y-m-d H:i:s')
            ]);
            return true;
        }
    }
}