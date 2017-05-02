<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class APIController extends Controller
{
    /**
     * Get root url.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndex(Application $app)
    {
        return new JsonResponse(['message' => $app->version()]);
    }
    public function test(Request $request){
        //return $request->all();
        $password = app('hash')->make('johndoe');
        echo $password;
        var_dump(app('hash')->check('johndoe',$password));
    }
}
