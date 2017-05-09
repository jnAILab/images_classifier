<?php
/**
 * Created by PhpStorm.
 * User: n
 * Date: 2017/4/26 0026
 * Time: 15:28
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $primaryKey = 'user_id';

    public $timestamps = false;

    public function getPerInformationToShow($user_id){
        $result = $this->select('user_id,realname,idcarnumber,address,telephone,user_points,like_image_class,icon_location')->where('user_id','=',$user_id)->get();
        return $result;
    }
}