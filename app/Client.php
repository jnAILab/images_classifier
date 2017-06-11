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

    public function getUserTaskLike($realname)
    {
        $result = $this->select('like_image_class')->where('realname',$realname)->get();
        return $result;
    }

    public function getPerInformationToShow($user_id){

        $result = DB::table('users')
            ->join('clients','clients.user_id','users.user_id')
            ->select('name','realname','sex','idcarnumber','email','like_image_class')
            ->where('users.user_id',$user_id)
            ->first();
        $result->like_image_class = json_decode($result->like_image_class);
        $array = [];
        foreach ($result->like_image_class as $id){
            $array[] = Category::where('category_id',$id)->select('category_name')->first()->category_name;
        }
        $result->like_image_class = $array;
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