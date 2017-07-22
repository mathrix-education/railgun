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

$app->group(['prefix' => 'users'], function () use ($app) {
    $app->post('register', 'UserController@register');
    $app->post('login', 'UserController@login');
    $app->post('refresh', 'UserController@refresh');

    // Protected routes
    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->get('', 'UserController@profile');
        $app->post('', 'UserController@account');
        $app->get('{id:[0-9]+}', 'UserController@profile');
    });
});

$app->group(['prefix' => '{countrySlug}/{classShortSlug}'], function () use ($app) {
    $app->get('', 'DisciplineController@index');

    $app->group(['prefix' => '{disciplineSlug}'], function () use ($app) {
        $app->get('', 'ChapterController@index');

        $app->group(['prefix' => '{chapterSlug:[a-z\-]+}-{chapterId:[0-9]+}'], function() use ($app) {
            $app->get('', 'NotionController@index');
            $app->get('{notionSlug:[a-z\-]+}-{notionId:[0-9]+}', 'ActivityController@index');
        });
    });
});