<?php
	
namespace App;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;

class Common extends Model
{
	  /**
         *
         * @author 聂恒奥
         *
         * 用来更新单个个人信息，可更新多个字段，
         * 或是管理员批量修改一组用户的信息，此功能前台需传user_id数组
         * 前台发送需要修改的信息，并以对应的字段名命名。例：
         * {
         *      'user_id':'1i0oha0h0k0as' or "name":["1i0oha0h0k0as","sadx5cxz5s1x2","askd5xzc154xz"]
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
         *                          'user_id':'1i0oha0h0k0as' or "name":["1i0oha0h0k0as","sadx5cxz5s1x2","askd5xzc154xz"]
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
        //if前台传入的user_id是数组，即为管理员更改多个用户的统一信息。
        if (is_array($request->input('user_id'))){

            $all_input = $all;
            unset($all_input['user_id']);
            //更新数据
            Client::whereIn('user_id',$request->input('user_id'))->update(
                $all_input
            );
            $ResultCode = 1;
        }
        //else传入的是user_id是字符串，即为用户更改个人信息。
        elseif(is_string($request->input('user_id'))){
            //提取数据，判断是管理员还是用户
            $user_all = User::where('user_id', '=', $request->input('user_id'))->first()['attributes'];
            $user_id = $user_all['user_id'];
            $if = $user_all['status'];
            //获取表中全部字段，为下面判断要更新字段所属表做准备。
            if ($if == 'admin'){
                $table_all = Admin::where('user_id', '=', $user_id)->first()['attributes'];
            }
            else{
                $table_all = Client::where('user_id', '=', $user_id)->first()['attributes'];
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
                        $ResultCode = Admin::where('user_id', '=', $user_id)->update(
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
            $ResultCode = 1;
        }
        else{
            $ResultMsg = '失败';
            $ResultCode = 0;
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
         *      'user_id':'1i0oha0h0k0as'
         *      'NewPassword':'shanshanshan'
         * }
         *
         * @param 接收控制器中创建的Request实例。
         * @return {
         *              'ResultCode':'1'or'0'
         *              'ResultMsg':'成功'or'失败'
         *              'Data':null
         *          }
         *
         *
         */

        $ResultCode = User::where('user_id', '=', $request->input('user_id'))->update(
            ['password' => md5($request->input('NewPassword')),'updated_at'=>date("Y-m-d h:i:s")]);
        if ($ResultCode){
            $ResultMsg = '成功';
            $ResultCode = 1;
        }
        else{
            $ResultMsg = '失败';
            $ResultCode = 0;
        }
        return ['ResultCode'=>$ResultCode,'ResultMsg'=>$ResultMsg,'Data'=>null];
    }

    /**
     * @author killer 2017年5月5日17:33:56
     * 用来返回相应前台的数据，返回的数据经过该函数进行格式化。
     * 成功相应时$status默认为200
     * 响应成功的例子：$common->returnJsonResponse(1,'token_generated',array('token' => $token));
     * 相应错误信息时，填写相应的状态码
     * 响应失败的例子：$common->returnJsonResponse(0,'could_not_create_token',
    null,Response::HTTP_INTERNAL_SERVER_ERROR);
     * @param $resultCode
     * @param $resultMsg
     * @param $data
     * @param int $status default 200
     * @return JsonResponse
     */
    public static function returnJsonResponse($resultCode,$resultMsg,$data,$status = 200){
        return new JsonResponse([
            'ResultCode' => $resultCode,
            'ResultMsg'  => $resultMsg,
            'Data' => $data
        ],$status);
    }
}