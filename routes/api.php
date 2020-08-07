<?php

Route::post('signup', 'AuthController@register')->middleware('cors');
Route::post('login', 'AuthController@login')->middleware('cors');

Route::group(['middleware' => 'jwt.auth'], function () {Route::get('auth', 'AuthController@user'); Route::post('logout', 'AuthController@logout');});

Route::middleware('jwt.refresh')->get('/token/refresh', 'AuthController@refresh');

Route::resource('task', 'TaskController');
Route::post('task-search', 'TaskController@search');
Route::post('task-total', 'TaskController@searchTotal');

Route::resource('board','BoardController')->only(['index','store','destroy']);
Route::post('/board-update','BoardController@update');
Route::post('/board-search','BoardController@search');