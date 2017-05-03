<?php

namespace App;

use Illuminate\Auth\Authenticatable;
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
     * 用来更加积分，在原积分基础上增加，可一次更加多个用户的积分，
     * 前台发送包含要更加积分的用户的name的names数组和，
     * 包含对应积分的user_points数组。例：
     * {
     *      'names':["kille","nie","shan"]
     *      'user_points':['10','20','30']
     * }
     *
     *
     * @param 接收控制器中创建的Request实例。
     * @return {
     *              'ResultCode':'1'or'0'
     *              'ResultMsg':'成功'or'失败'
     *              'Data'=>{
     *                          'shan':'10'
     *                          'cao':'20'
     *                      }
     *
     *
     */
    public static function increaseUserPoints($request){
		
        $names = $request->input('names');
        $user_points = $request->input('user_points');

        $i = 0;         //计数
        $Result = array();
        //在循环中遍历所有用户，完成更加积分。
        foreach ($names as $name){
            $user_id = User::where('name', '=', $name)->first()['attributes']['user_id'];
            $user_point = Client::where('user_id', '=', $user_id)->first()["attributes"]['user_points'];
            $user_point = $user_point+intval($user_points[$i]);
            $ResultCode = Client::where('user_id', '=', $user_id)->update(
                ['user_points' => $user_point,'updated_at'=>date("Y-m-d h:i:s")]);
            $Result[$name] = $user_point;
            $i++;
        }
        if ($ResultCode){
            $ResultMsg = '成功';
        }
        else{
            $ResultMsg = '失败';
            $user_points = null;
        }
        return ['ResultCode'=>$ResultCode,'ResultMsg'=>$ResultMsg,'Data'=>$Result];
    }
}
