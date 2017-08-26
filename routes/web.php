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


/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/
$app->group(['prefix' => 'users'], function () use ($app) {
    $app->post('register', 'UserController@register');
    $app->post('login', 'AuthController@login');
    $app->post('refresh', 'AuthController@refresh');
    $app->post('forgot', 'AuthController@forgot');
    $app->post('reset/{token}', 'AuthController@reset');

    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->get('', 'UserController@index');
        $app->get('{page:[0-9]+}/{perPage:[0-9]+}', 'UserController@index');
        $app->get('search/{field}/{like}', 'UserController@search');
        $app->get('me', 'UserController@me');
        $app->get('{id:[0-9]+}', 'UserController@get');
        $app->patch('{id:[0-9]+}', 'UserController@edit');
        $app->delete('{id:[0-9]+}', 'UserController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Content
|--------------------------------------------------------------------------
*/
$app->group(['prefix' => 'content'], function () use ($app) {
    $app->get('countries', 'ContentController@countries');
    $app->get('program/{classId:[0-9]+}', 'ContentController@program');
    $app->get('modules/{activityId:[0-9]+}', 'ContentController@modules');
});


/*
|--------------------------------------------------------------------------
| Countries
|--------------------------------------------------------------------------
*/
$app->group(['prefix' => 'countries'], function () use ($app) {
    $app->get('', 'CountryController@index');
    $app->get('{id:[0-9]+}', 'CountryController@get');

    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->post('', 'CountryController@add');
        $app->patch('{id:[0-9]+}', 'CountryController@edit');
        $app->delete('{id:[0-9]+}', 'CountryController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Classes
|--------------------------------------------------------------------------
*/
$app->group(['prefix' => 'classes'], function () use ($app) {
    $app->get('', 'ClassController@index');
    $app->get('{id:[0-9]+}', 'ClassController@get');

    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->post('', 'ClassController@add');
        $app->patch('{id:[0-9]+}', 'ClassController@edit');
        $app->delete('{id:[0-9]+}', 'ClassController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Disciplines
|--------------------------------------------------------------------------
*/
$app->group(['prefix' => 'disciplines'], function () use ($app) {
    $app->get('', 'DisciplineController@index');
    $app->get('{id:[0-9]+}', 'DisciplineController@get');

    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->post('', 'DisciplineController@add');
        $app->patch('{id:[0-9]+}', 'DisciplineController@edit');
        $app->delete('{id:[0-9]+}', 'DisciplineController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Chapters
|--------------------------------------------------------------------------
*/
$app->group(['prefix' => 'chapters'], function () use ($app) {
    $app->get('', 'ChapterController@index');
    $app->get('{id:[0-9]+}', 'ChapterController@get');

    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->post('', 'ChapterController@add');
        $app->patch('{id:[0-9]+}', 'ChapterController@edit');
        $app->delete('{id:[0-9]+}', 'ChapterController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Notions
|--------------------------------------------------------------------------
*/
$app->group(['prefix' => 'notions'], function () use ($app) {
    $app->get('', 'NotionController@index');
    $app->get('{id:[0-9]+}', 'NotionController@get');

    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->post('', 'NotionController@add');
        $app->patch('{id:[0-9]+}', 'NotionController@edit');
        $app->delete('{id:[0-9]+}', 'NotionController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Activities
|--------------------------------------------------------------------------
*/
$app->group(['prefix' => 'activities'], function () use ($app) {
    $app->get('', 'ActivityController@index');
    $app->get('{id:[0-9]+}', 'ActivityController@get');

    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->post('', 'ActivityController@add');
        $app->patch('{id:[0-9]+}', 'ActivityController@edit');
        $app->delete('{id:[0-9]+}', 'ActivityController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Modules
|--------------------------------------------------------------------------
*/
$app->group(['prefix' => 'modules'], function () use ($app) {
    $app->get('', 'ModuleController@index');
    $app->get('{id:[0-9]+}', 'ModuleController@get');

    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->post('', 'ModuleController@add');
        $app->patch('{id:[0-9]+}', 'ModuleController@edit');
        $app->delete('{id:[0-9]+}', 'ModuleController@delete');
    });
});

/*
|--------------------------------------------------------------------------
| Subscriptions
|--------------------------------------------------------------------------
*/
$app->group(['prefix' => 'stripe'], function () use ($app) {
    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->post('subscribe', 'StripeController@subscribe');
    });

    $app->post('charge', 'StripeController@charge');

    $app->post('webhook', 'StripeController@handleWebhook');
});