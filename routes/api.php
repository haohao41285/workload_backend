<?php

Route::post('signup', 'AuthController@register');
Route::post('login', 'AuthController@login');

Route::group(['middleware' => 'jwt.auth'], function () {
	Route::get('auth', 'AuthController@user');
	Route::post('logout', 'AuthController@logout');
});

Route::middleware('jwt.refresh')->get('/token/refresh', 'AuthController@refresh');

//Tasks
Route::post('task-search', 'TaskController@search');
Route::post('task-total', 'TaskController@searchTotal');
Route::get('detail_task/{id_board}', 'TaskController@detail_task');
Route::post('task-calculate', 'TaskController@calculate');
Route::post('extend', 'TaskController@extendTask');
Route::group(['prefix' => 'task'], function () {
	Route::get('by-token/{token}', 'TaskController@byToken');
	Route::post('response-extend', 'TaskController@responseExtend');
});
Route::resource('task', 'TaskController');

//Boards
Route::group(['prefix' => 'board'], function () {
	Route::post('/users', 'BoardController@users');
	Route::post('/lists', 'BoardController@show');
	Route::post('update-id-trello-to-user', 'BoardController@updateIdTrelloToUser');
});
Route::resource('board', 'BoardController')->only(['index', 'store', 'destroy']);
Route::post('/board-update', 'BoardController@update');
Route::post('/board-search', 'BoardController@search');

//Users
Route::group(['prefix' => 'user'], function () {
	Route::post('{id}/change-password', 'UserController@changePassword')->where(['id' => '[0-9]']);
	Route::post('{id}/update-status', 'UserController@updateStatus');
	Route::get('{id}/get-one', 'UserController@getOne');
	Route::post('{id}/update-one', 'UserController@updateOne');
	Route::post('{id}/update-password', 'UserController@updatePassword');
});
Route::resource('user', 'UserController')->only(['show', 'update', 'destroy', 'index']);

Route::post('user-search', 'UserController@search');
//Teams
Route::group(['prefix' => 'team'], function () {
	Route::post('search', 'TeamController@search');
	Route::get('leader', 'TeamController@teamsLeader');
	Route::post('add-user-to-team', 'TeamController@addUserToTeam');
	Route::post('remove-user-out-team', 'TeamController@removeUserOutTeam');
});
Route::resource('team', 'TeamController');

//Task Logs
Route::resource('log', 'LogTaskController')->only(['store', 'index', 'show']);
//Report
Route::group(['prefix' => 'report'], function () {
	Route::post('/search', 'ReportController@search');
	Route::post('/get-user', 'ReportController@getUser');
});

//Project
Route::group(['prefix' => 'project'], function () {
	Route::post('/search', 'ProjectController@search');
});
Route::resource('project', 'ProjectController');

Route::group(['prefix' => 'role'], function () {
	Route::post('search', 'RoleController@search');
});

//Role
Route::resource('role', 'RoleController')->only(['index', 'store', 'update']);

//Permission
Route::resource('permission', 'PermissionController')->only(['update', 'index']);
