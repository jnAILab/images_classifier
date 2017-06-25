<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
    ];

    protected $primaryKey = 'auto_id';  //指定主键

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    
    /**
     *
     * @author 聂恒奥
     *
     * 用来更加积分，在原积分基础上增加，可一次更加多个用户的积分。
     *
     *
     * @param $user_ids
     * @param $user_points
     *
     * @return $ResultCode
     *
     *
     *
     */
    public function increaseUserPoints($user_id,$user_points){
//        $i = 0;         //计数
//        foreach ($user_ids as $user_id){    //在循环中遍历所有用户，完成更加积分。
//            $user_point = Client::where('user_id', '=', $user_id)->first()["attributes"]['user_points'];
//            $user_point = $user_point+intval($user_points[$i]);
//            $ResultCode = Client::where('user_id', '=', $user_id)->update(
//                ['user_points' => $user_point,'updated_at'=>date("Y-m-d h:i:s")]);
//            $i++;
//        }
        $user_point = Client::where('user_id', '=', $user_id)->first()->user_points;
        $user_point = $user_point+intval($user_points);
        $ResultCode = Client::where('user_id', '=', $user_id)->update(
            ['user_points' => $user_point,'updated_at'=>date("Y-m-d h:i:s")]
        );

        return $ResultCode;
    }

    /*
     * @auth 范留山
     * 显示用户列表
     * **/
    public function showUserList(){
        $clients = DB::table('clients')
            ->join('users','users.user_id','clients.user_id')
            ->select(
                'users.user_id',
                'clients.sex',
                'clients.created_at',
                'users.name',
                'users.email'
            )->where('users.status','client')
            ->get()
       ->toArray();

        return $clients;
    }

    function registerUser($name,$email,$password,$status){
        $user_id = uniqid();
        $selectResult = $this->where('email','=',$email)->get();
        if(count($selectResult)!=0){
            return false;
        }
        try{
            $this->insert([
                'user_id'=>$user_id,
                'name' => $name,
                'email' =>$email,
                'password' => app('hash')->make($password),
                'remember_token' => str_random(10),
                'status'=>$status,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s'),
                'last_login_ip'=>$_SERVER["REMOTE_ADDR"]
            ]);
        }catch (Exception $e){
            return false;
        }
        return $user_id;
    }


}
