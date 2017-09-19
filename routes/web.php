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

$app->get('/', function () use ($app) {
  $res['success'] = true;
  $res['result'] = 'Hello world';
  return response($res);
});

// route for creating access_token
$app->post('accessToken', 'AccessTokenController@createAccessToken');

$app->get('/user/{id}', 'UserController@get_user');

$app->group(['middleware' => 'auth:api'], function () use ($app) {
    $app->post('users', 'UserController@store');
    $app->get('users', 'UserController@index');
    $app->get('users/{id}', 'UserController@get_user');
    $app->put('users/{id}', 'UserController@update');
    $app->delete('users/{id}', 'UserController@destroy');
});
