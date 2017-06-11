<?php

namespace App\Http\Controllers\Auth;
use App\Common;
use App\User;
use App\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Exception\HttpResponseException;

class AuthController extends Controller
{
    public function postRegister(Request $request){
        //这个接口是用来注册用户的，因此身份全部为用户
        $realname=$request -> input("realname");
        $idcarNumber=$request -> input("idcarnumber");
        $sex=$request -> input("sex");
        $email = $request -> input("email");
        $userName = $request -> input("name");
        $password = $request -> input("password");
        $status = 'client';
        $userObj = new User();
        //在user表里面注册信息。
        $user_id = $userObj->registerUser($userName,$email,$password,$status);
        if(!$user_id){
            //邮箱重复了
            return Common::returnJsonResponse(0,'email repeated',null);
        }
        $clientObj = new Client();
        $result = $clientObj->registerClient($realname,$idcarNumber,$sex,$user_id);
        if($result){
            return Common::returnJsonResponse(1,'register successfully',null);
        }else{
            return Common::returnJsonResponse(0,'failed to register user',null);
        }
    }
    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        try {
            $this->validatePostLoginRequest($request);
        } catch (HttpResponseException $e) {
            return $this->onBadRequest();
        }

        try {
            // Attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt(
                $this->getCredentials($request)
            )) {
                return $this->onUnauthorized();
            }
        } catch (JWTException $e) {
            // Something went wrong whilst attempting to encode the token
            return $this->onJwtGenerationError();
        }
        $email = $request -> input("email");
        // All good so return the token
        return $this->onAuthorized($token,$email);
    }

    /**
     * Validate authentication request.
     *
     * @param  Request $request
     * @return void
     * @throws HttpResponseException
     */
    protected function validatePostLoginRequest(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);
    }

    /**
     * What response should be returned on bad request.
     *
     * @return JsonResponse
     */
    protected function onBadRequest()
    {
        return Common::returnJsonResponse(0,'invalid_credentials',
            null,Response::HTTP_BAD_REQUEST);
//        return new JsonResponse([
//            'message' => 'invalid_credentials'
//        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * What response should be returned on invalid credentials.
     *
     * @return JsonResponse
     */
    protected function onUnauthorized()
    {

        return Common::returnJsonResponse(0,'invalid_credentials',
            null,Response::HTTP_UNAUTHORIZED);
//        return new JsonResponse([
//            'message' => 'invalid_credentials'
//        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * What response should be returned on error while generate JWT.
     *
     * @return JsonResponse
     */
    protected function onJwtGenerationError()
    {
        return Common::returnJsonResponse(0,'could_not_create_token',
            null,Response::HTTP_INTERNAL_SERVER_ERROR);
        //return new JsonResponse([
        //'message' =>
        //], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * What response should be returned on authorized.
     * 返回认证用户的信息
     *
     * @return JsonResponse
     */
    protected function onAuthorized($token,$email)
    {
        $userObj = new User();
        $user = $userObj->where('email','=',$email)->first();
        return Common::returnJsonResponse(1,'token_generated',array('token' => $token,'user_id'=>$user->user_id));
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function getCredentials(Request $request)
    {
        //print_R($request->only('email', 'password'));
        return $request->only('email', 'password');
    }

    /**
     * Invalidate a token.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteInvalidate()
    {
        $token = JWTAuth::parseToken();

        $token->invalidate();
        return Common::returnJsonResponse(1,'token_invalidated',null);
        //return new JsonResponse(['message' => 'token_invalidated']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\Response
     */
    public function patchRefresh()
    {
        $token = JWTAuth::parseToken();

        $newToken = $token->refresh();
        return Common::returnJsonResponse(1,'token_refreshed',array('token' => $newToken));
//        return new JsonResponse([
//            'message' => 'token_refreshed',
//            'data' => [
//                'token' => $newToken
//            ]
//        ]);
    }

    /**
     * Get authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUser()
    {

        return Common::returnJsonResponse(1,'authenticated_user',JWTAuth::parseToken()->authenticate());
//        return new JsonResponse([
//            'message' => 'authenticated_user',
//            'data' =>
//        ]);
    }
}
