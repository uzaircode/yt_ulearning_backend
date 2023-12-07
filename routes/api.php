<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['namespace' => 'Api'], function () {
  Route::post('/login', 'UserController@createUser');
  Route::group(['middleware' => ['auth:sanctum']], function() {
    Route::any('/courseList', 'CourseController@courseList');
    Route::any('/courseDetail', 'CourseController@courseDetail');
    Route::any('/checkout', 'PayController@checkout');
  });
  //run --> ngrok http 8000 --domain thoroughly-causal-chipmunk.ngrok-free.app
  //https://thoroughly-causal-chipmunk.ngrok-free.app
  Route::any('/web_go_hooks', 'PayController@web_go_hooks');
});