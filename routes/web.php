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
    return response()->json([
        'message' => 'Welcome to Mathrix API version 1.0'
    ]);
});

require_once 'admin.php';

$app->group(['prefix' => 'users'], function () use ($app) {
    $app->post('register', 'UserController@register');
    $app->post('login', 'AuthController@login');
    $app->post('refresh', 'AuthController@refresh');
    $app->post('forgot', 'AuthController@forgot');
    $app->post('reset/{token}', 'AuthController@reset');

    // Protected routes
    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->get('', 'UserController@index');
        $app->get('{page:[0-9]+}/{perPage:[0-9]+}', 'UserController@index');
        $app->get('me', 'UserController@me');
        $app->get('{id:[0-9]+}', 'UserController@get');
        $app->patch('{id:[0-9]+}', 'UserController@edit');
        $app->delete('{id:[0-9]+}', 'UserController@delete');
    });
});

$app->group(['prefix' => 'classes'], function() use ($app) {
    $app->get('{country:[a-z]+}', 'ClassController@index');
});

/*$app->group(['prefix' => '{country:[a-z]+}'], function () use ($app) {


    $app->group(['prefix' => '{class:[a-z0-9\-]+}'], function() use ($app) {
        $app->get('', 'DisciplineController@index');

        $app->group(['prefix' => '{discipline:[a-z\-]+}'], function () use ($app) {
            $app->get('', 'ChapterController@index');

            $app->group(['prefix' => '{chapterSlug:[a-z\-]+}-{chapter:[0-9]+}'], function() use ($app) {
                $app->get('', 'NotionController@index');

                $app->group(['prefix' => '{notionSlug:[a-z\-]+}-{notion:[0-9]+}'], function() use ($app) {
                    $app->get('', 'ActivityController@index');
                });
            });
        });
    });
});*/