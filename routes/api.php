<?php

Route::post('signup', 'AuthController@register');
Route::post('login', 'AuthController@login');

Route::group(['middleware' => 'jwt.auth'], function () {
	Route::get('auth', 'AuthController@user');
	Route::post('logout', 'AuthController@logout');
});

Route::middleware('jwt.refresh')->get('/token/refresh', 'AuthController@refresh');

//Tasks
Route::resource('task', 'TaskController');
Route::post('task-search', 'TaskController@search');
Route::post('task-total', 'TaskController@searchTotal');
Route::get('detail_task/{id_board}', 'TaskController@detail_task');
Route::post('task-calculate', 'TaskController@calculate');
Route::post('extend', 'TaskController@extendTask');

//Boards
Route::group(['prefix' => 'board'], function () {
	Route::get('/{id}/users', 'BoardController@users');
	Route::get('/{id}/lists', 'BoardController@show');
	Route::post('update-id-trello-to-user', 'BoardController@updateIdTrelloToUser');
});
Route::resource('board', 'BoardController')->only(['index', 'store', 'destroy']);
Route::post('/board-update', 'BoardController@update');
Route::post('/board-search', 'BoardController@search');

//Users
Route::resource('user', 'UserController')->only(['update', 'destroy', 'index']);

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
Route::resource('log', 'LogTaskController')->only(['store', 'index']);
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