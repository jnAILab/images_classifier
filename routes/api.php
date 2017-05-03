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

    $api->get('/test', [
        'uses' => 'App\Http\Controllers\APIController@test',
        'as' => 'api.test'
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
    });
    
    /**
	*author 范留山
	*/
	//创建一个任务
	$api->post('/createTask',[
		'uses' => 'TaskController@createTasks',
		'middleware' => 'createOneTask',
		]);

	//查看一个任务信息
	$api->post('/taskInformation',[
		'uses' => 'TaskController@getTasks',
		]);

	//查看任务列表
	$api->post('/taskList',[
		'uses' => 'TaskController@getTaskList',
		]);
	//更新一个任务信息
	$api->post('/updateTask',[
		'uses' => 'TaskController@updateTask',
		]);
		
	//删除任务信息（可批量）
	$api->post('/delectTask',[
		'uses' => 'TaskController@delectTask',
	]);


	/**
	*author 聂恒奥
	*/
	$api->post('changePassword',[
		'uses'=>'PersonController@changePassword'
	]);
	$api->post('increaseUserPoints',[
	'uses'=>'PersonController@increaseUserPoints'
	]);
	$api->post('updatePersonInformation',[
	'uses'=>'PersonController@updatePersonInformation'
	]);


	/**
	*author 葛操
	*/
	$api->get('getCategoryList',[
		'CategoryController@getCategoryList'
	]);
	$api->post('storegetCategories',[
		'CategoryController@storegetCategories'
	]);
	$api->post('updateCategoryNames',[
		'CategoryController@updateCategoryNames'
	]);
	$api->post('deleteCategories',[
		'CategoryController@deleteCategories'
	]);

	/**
	*author 范留山
	*/
	//添加管理员
	$api->post('/addAdministrator',[
	'uses'=>'PersonController@addAdmin',
	'middleware' => 'addAdmin'
	]);
	/**
	*author 田荣鑫
	*/
	$api->post('deladmin',[
		'PersonController@deleteAdministrators'
	]);
	$api->get('getadminlist',[
		'PersonController@getAdministratorList'
	]);
	$api->post('alteradminpsd',[
		'PersonController@alterAdminPsd'
	]);

	/**
	*author 张正茂
	*/
	$api->post('store',[
		'LabelController@storeLabelContent'
	]);
	$api->post('get',[
		'LabelController@getLabelContent'
	]);
	$api->post('update',[
		'LabelController@updateLabelContent'
	]);
	$api->post('delete',[
		'LabelController@deleteLabel'
	]);
    
});
