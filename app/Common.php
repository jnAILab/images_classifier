<?php
	
namespace App;


use Illuminate\Database\Eloquent\Model;

class Common extends Model
{
	  /**
         *
         * @author 聂恒奥
         *
         * 用来更新单个个人信息，可更新多个字段，
         * 或是管理员批量修改一组用户的信息，此功能前台需传name数组
         * 前台发送需要修改的信息，并以对应的字段名命名。例：
         * {
         *      'name':'shan' or "name":["kille","nie","shan"]
         *       ...
         *      'user_points':'50'
         *       ...
         *      'telemphone':'17864738436'
         * }
         *
         *
         * @param 接收控制器中创建的Request实例。
         * @return {
         *              'ResultCode':'1'or'0'
         *              'ResultMsg':'成功'or'失败'
         *              'Data':{
         *                          'name':'shan' or "name":["kille","nie","shan"]
         *                          ...
         *                          'user_points':'50'
         *                          ...
         *                          'telemphone':'17864738436'
         *                      }
         *
         *
         */
    public static function updatePersonInformation($request){
    	
        $all = $request->all();
        $ResultCode = 0;
        //if前台传入的name是数组，即为管理员更改多个用户的统一信息。
        if (is_array($request->input('name'))){
            $names = $request->input('name');
            $user_ids = array();
            //循环得到所有用户的id。
            foreach ($names as $name){
                $user_ids[] = User::where('name', $name)->first()['attributes']['user_id'];
            }
            $all_input = $all;
            unset($all_input['name']);
            //更新数据
            Client::whereIn('user_id',$user_ids)->update(
                $all_input
            );
            $ResultCode = 1;
        }
        //else传入的是name是字符串，即为用户更改个人信息。
        elseif(is_string($request->input('name'))){
            //提取数据，判断是管理员还是用户
            $user_all = User::where('name', '=', $request->input('name'))->first()['attributes'];
            $user_id = $user_all['user_id'];
            $if = $user_all['status'];
            //获取表中全部字段，为下面判断要更新字段所属表做准备。
            if ($if == 'admin'){
                $table_all = Admins::find($user_id)['attributes'];
            }
            else{
                $table_all = Client::find($user_id)['attributes'];
            }
            //筛选数据，判断要更新的字段所属的表，并完成更新。
            foreach ($all as $key=>$value){
                if (array_key_exists($key,$user_all)){
                    $ResultCode = User::where('user_id', '=', $user_id)->update(
                        [$key=>$value,'updated_at'=>date("Y-m-d h:i:s")]
                    );
                }
                elseif (array_key_exists($key,$table_all)){
                    if ($if == 'admin'){
                        $ResultCode = Admins::where('user_id', '=', $user_id)->update(
                            [$key=>$value,'updated_at'=>date("Y-m-d h:i:s")]
                        );
                    }
                    else{
                        $ResultCode = Client::where('user_id', '=', $user_id)->update(
                            [$key=>$value,'updated_at'=>date("Y-m-d h:i:s")]
                        );
                    }
                }
            }
        }
        if ($ResultCode){
            $ResultMsg = '成功';
        }
        else{
            $ResultMsg = '失败';
            $all = null;
        }
        return ['ResultCode'=>$ResultCode,'ResultMsg'=>$ResultMsg,'Data'=>$all];
    }
    public static function changePassword($request){

        /**
         *
         * @author 聂恒奥
         *
         * 用来修改密码，加密后更新进数据库，前台发送要更改的新密码。例：
         * {
         *      'name':'shan'
         *      'NewPassword':'shanshanshan'
         * }
         *
         * @param 接收控制器中创建的Request实例。
         * @return {
         *              'ResultCode':'1'or'0'
         *              'ResultMsg':'成功'or'失败'
         *              'Data':null
         *
         *
         */

        $ResultCode = User::where('name', '=', $request->input('name'))->update(
            ['password' => md5($request->input('NewPassword')),'updated_at'=>date("Y-m-d h:i:s")]);
        if ($ResultCode){
            $ResultMsg = '成功';
        }
        else{
            $ResultMsg = '失败';
        }
        return ['ResultCode'=>$ResultCode,'ResultMsg'=>$ResultMsg,'Data'=>null];
    }
}