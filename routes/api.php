<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$api = $app->make(Dingo\Api\Routing\Router::class);



$api->version('v1', function ($api) {
    $api->post('/auth/login', [
        'as' => 'api.auth.login',
        'uses' => 'App\Http\Controllers\Auth\AuthController@postLogin',
    ]);

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/', [
            'uses' => 'App\Http\Controllers\APIController@getIndex',
            'as' => 'api.index'
        ]);
        $api->get('/auth/user', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@getUser',
            'as' => 'api.auth.user'
        ]);
        $api->patch('/auth/refresh', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@patchRefresh',
            'as' => 'api.auth.refresh'
        ]);
        $api->delete('/auth/invalidate', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@deleteInvalidate',
            'as' => 'api.auth.invalidate'
        ]);
       /**
	*author 范留山
	*/
        //添加管理员
        $api->post('/addAdministrator',[
            'uses'=>'App\Http\Controllers\PersonController@addAdmin',
            'middleware' => 'addAdmin'
        ]);
        
        /**
	*author 范留山
	*/
//	//创建一个任务
//	$api->post('/createTask',[
//		'uses' => 'App\Http\Controllers\TaskController@createTasks',
//		'middleware' => 'createOneTask',
//	]);

	//查看一个任务信息
	$api->post('/taskInformation',[
		'uses' => 'App\Http\Controllers\TaskController@getTasks',
	]);

	//查看任务列表
	$api->post('/taskList',[
		'uses' => 'App\Http\Controllers\TaskController@getTaskList',
	]);
	//更新一个任务信息
	$api->post('/updateTask',[
		'uses' => 'App\Http\Controllers\TaskController@updateTask',
	]);
		
	//删除任务信息（可批量）
	$api->post('/delectTask',[
		'uses' => 'App\Http\Controllers\TaskController@delectTask',
	]);

        /**
         *author 张政茂
         */
        $api->post('storeLabelContent',[
            'uses'=>'App\Http\Controllers\LabelController@storeLabelContent'
        ]);
        $api->post('getLabelContent',[
            'uses'=>'App\Http\Controllers\LabelController@getLabelContent'
        ]);
        $api->post('updateLabelContent',[
            'uses'=>'App\Http\Controllers\LabelController@updateLabelContent'
        ]);
        $api->post('deleteLabel',[
            'uses'=>'App\Http\Controllers\LabelController@deleteLabel'
        ]);

    /**
     *author 聂恒奥
     */
    //修改信息
    $api->post('updatePersonInformation',[
        'uses'=>'App\Http\Controllers\PersonController@updatePersonInformation'
    ]);
    //更加积分
    $api->post('increaseUserPoints',[
        'uses'=>'App\Http\Controllers\PersonController@increaseUserPoints'
    ]);
    //修改密码
    $api->post('changePassword',[
        'uses'=>'App\Http\Controllers\PersonController@changePassword'
    ]);
        
        	/**
	*author 葛操
	*/
	$api->post('getCategoryList',[
		'uses'=>'App\Http\Controllers\CategoryController@getCategoryList'
	]);
	$api->post('storegetCategories',[
		'uses'=>'App\Http\Controllers\CategoryController@storegetCategories',
		'middleware'=>'storeCategory'
	]);
	$api->post('updateCategoryNames',[
		'uses'=>'App\Http\Controllers\CategoryController@updateCategoryNames'
	]);
	$api->post('deleteCategories',[
		'uses'=>'App\Http\Controllers\CategoryController@deleteCategories'
	]);


        /**
         *@author 田荣鑫
         */
        $api->post('deladmin',[
            'uses'=>'App\Http\Controllers\PersonController@deleteAdministrators'
        ]);
        $api->get('getadminlist',[
            'uses'=>'App\Http\Controllers\PersonController@getAdministratorList'
        ]);
        $api->post('alteradminpsd',[
            'uses'=>'App\Http\Controllers\PersonController@alterAdminPsd'
        ]);
        /**
         *@author killer
         */
        $api->get('pushImage',[
            'uses'=>'App\Http\Controllers\ImageController@pushImageToUser',
        ]);
        $api->post('recordLabelWithImage',[
            'uses'=>'App\Http\Controllers\LabelController@recordLabelWithImage',
        ]);
        $api->post('likeLabelWithImage',[
            'uses'=>'App\Http\Controllers\LabelController@likeLabelWithImage',
        ]);
        $api->post('oppositeLabelWithImage',[
            'uses'=>'App\Http\Controllers\LabelController@oppositeLabelWithImage',
        ]);

        /*范留山s*/
        //将图片id 和点赞前三个的标签提取出来，形成excel表格，第一列是image_id,第二列标签，第三列标签id……
        $api->get('/imageExecl', [
            'uses' => 'App\Http\Controllers\LabelController@imageExecl'
        ]);
        //显示图片标记信息的函数
        $api->post('/getImageMarkedInformation',[
            "uses"=>'App\Http\Controllers\ImageController@getImageMarkedInformation'
        ]);
        //获取某个类别里面未被标记的图片列表
        $api->post('/getImageUnmarkedList',[
            "uses"=>'App\Http\Controllers\ImageController@getImageUnmarkedList'
        ]);
        //获取某个类别里面未被标记的图片列表
        $api->post('/getImageMarkedList',[
            "uses"=>'App\Http\Controllers\ImageController@getImageMarkedList'
        ]);

    });



});
