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
    return $app->version();
});

$app->post('users/register', 'UserController@register');
$app->post('users/login', 'UserController@login');
$app->post('users/refresh', 'UserController@refresh');

// Protected routes
$app->group(['middleware' => 'auth:api'], function () use ($app) {
    $app->get('users', 'UserController@profile');
    $app->get('users/{id:[0-9]+}', 'UserController@profile');
});