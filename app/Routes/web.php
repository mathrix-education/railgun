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

use Laravel\Lumen\Routing\Router;

/** @var Router $router */

$router->get('/', function () use ($router) {
    return response()->json([
        'message' => 'Welcome to Mathrix API version 0.8.1 by Mathieu Bour',
        'commit' => trim(exec('git log --pretty="%h" -n1 HEAD')),
        'build' => basename(dirname(__DIR__, 2))
    ]);
});


/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'users'], function () use ($router) {
    $router->post('register', 'UserController@register');
    $router->post('login', 'AuthController@login');
    $router->post('refresh', 'AuthController@refresh');
    $router->post('forgot', 'AuthController@forgot');
    $router->post('reset/{token}', 'AuthController@reset');
    $router->get('exists/{field}/{value}', 'UserController@exists');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->get('', 'UserController@index');
        $router->get('me', 'UserController@me');

        $router->get('{page:[0-9]+}/{perPage:[0-9]+}', 'UserController@index');
        $router->get('{id:[0-9]+}', 'UserController@get');
        $router->patch('{id:[0-9]+}', 'UserController@edit');
        $router->delete('{id:[0-9]+}', 'UserController@delete');
        $router->post('search', 'UserController@search');

        $router->post('points', 'UserController@points');
        $router->post('points/{id:[0-9]+}', 'UserController@points');
    });
});


/*
|--------------------------------------------------------------------------
| Content
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'content'], function () use ($router) {
    $router->get('countries', 'ContentController@countries');
    $router->get('program/{classId:[0-9]+}', 'ContentController@program');
    $router->get('modules/{activityId:[0-9]+}', 'ContentController@modules');
    $router->get('search/{query}', 'ContentController@searchGlobal');
    $router->get('search/{query}/{page:[0-9]+}/{perPage:[0-9]+}', 'ContentController@searchGlobal');
});


/*
|--------------------------------------------------------------------------
| Countries
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'countries'], function () use ($router) {
    $router->get('', 'CountryController@index');
    $router->get('{id:[0-9]+}', 'CountryController@get');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('', 'CountryController@add');
        $router->patch('{id:[0-9]+}', 'CountryController@edit');
        $router->delete('{id:[0-9]+}', 'CountryController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Classes
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'classes'], function () use ($router) {
    $router->get('', 'ClassController@index');
    $router->get('{id:[0-9]+}', 'ClassController@get');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('', 'ClassController@add');
        $router->patch('{id:[0-9]+}', 'ClassController@edit');
        $router->delete('{id:[0-9]+}', 'ClassController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Disciplines
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'disciplines'], function () use ($router) {
    $router->get('', 'DisciplineController@index');
    $router->get('{id:[0-9]+}', 'DisciplineController@get');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('', 'DisciplineController@add');
        $router->patch('{id:[0-9]+}', 'DisciplineController@edit');
        $router->delete('{id:[0-9]+}', 'DisciplineController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Chapters
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'chapters'], function () use ($router) {
    $router->get('{id:[0-9]+}', 'ChapterController@get');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->get('', 'ChapterController@index');
        $router->post('', 'ChapterController@add');
        $router->patch('{id:[0-9]+}', 'ChapterController@edit');
        $router->delete('{id:[0-9]+}', 'ChapterController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Notions
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'notions'], function () use ($router) {
    $router->get('', 'NotionController@index');
    $router->get('{id:[0-9]+}', 'NotionController@get');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('', 'NotionController@add');
        $router->patch('{id:[0-9]+}', 'NotionController@edit');
        $router->delete('{id:[0-9]+}', 'NotionController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Activities
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'activities'], function () use ($router) {
    $router->get('{id:[0-9]+}', 'ActivityController@get');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->get('', 'ActivityController@index');
        $router->post('', 'ActivityController@add');
        $router->patch('{id:[0-9]+}', 'ActivityController@edit');
        $router->delete('{id:[0-9]+}', 'ActivityController@delete');
        $router->post('search', 'ActivityController@search');

        $router->get('favorites', 'ActivityController@favorites');
        $router->get('to-revise', 'ActivityController@to_revise');
        $router->patch('{id:[0-9]+}/mark', 'ActivityController@mark');
    });
});


/*
|--------------------------------------------------------------------------
| Modules
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'modules'], function () use ($router) {
    $router->get('', 'ModuleController@index');
    $router->get('{id:[0-9]+}', 'ModuleController@get');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('', 'ModuleController@add');
        $router->patch('{id:[0-9]+}', 'ModuleController@edit');
        $router->delete('{id:[0-9]+}', 'ModuleController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Posts
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'posts'], function () use ($router) {
    $router->get('', 'PostController@index');
    $router->get('{page:[0-9]+}/{perPage:[0-9]+}', 'PostController@index');
    $router->get('{id:[0-9]+}', 'PostController@get');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('', 'PostController@add');
        $router->patch('{id:[0-9]+}', 'PostController@edit');
        $router->delete('{id:[0-9]+}', 'PostController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Courses
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'courses'], function () use ($router) {
    $router->get('', 'CourseController@index');
    $router->get('{id:[0-9]+}', 'CourseController@get');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('', 'CourseController@add');
        $router->patch('{id:[0-9]+}', 'CourseController@edit');
        $router->delete('{id:[0-9]+}', 'CourseController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'categories'], function () use ($router) {
    $router->get('', 'CategoryController@index');
    $router->get('{id:[0-9]+}', 'CategoryController@get');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('', 'CategoryController@add');
        $router->patch('{id:[0-9]+}', 'CategoryController@edit');
        $router->delete('{id:[0-9]+}', 'CategoryController@delete');
    });
});


/*
|--------------------------------------------------------------------------
| Comments
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'comments'], function () use ($router) {
    $router->get('{id:[0-9]+}', 'CommentController@get');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->get('', 'CommentController@index');
        $router->post('', 'CommentController@add');
        $router->patch('{id:[0-9]+}', 'CommentController@edit');
        $router->delete('{id:[0-9]+}', 'CommentController@delete');
    });

    $router->get('{commentable_type}/{commentable_id:[0-9]+}', 'CommentController@type');
    $router->get('{commentable_type}/{commentable_id:[0-9]+}/{page:[0-9]+}/{perPage:[0-9]+}',
        'CommentController@type');
});


/*
|--------------------------------------------------------------------------
| Votes
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'votes'], function () use ($router) {
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('{votable_type}/{votable_id}/up', 'VoteController@up');
        $router->post('{votable_type}/{votable_id}/down', 'VoteController@down');
    });
});


/*
|--------------------------------------------------------------------------
| Quotes
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'quotes'], function () use ($router) {
    $router->get('', 'QuoteController@index');
    $router->get('{id:[0-9]+}', 'QuoteController@get');
    $router->get('random', 'QuoteController@random');
    $router->get('random/{count:[0-9]+}', 'QuoteController@random');

    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('', 'QuoteController@add');
        $router->patch('{id:[0-9]+}', 'QuoteController@edit');
        $router->delete('{id:[0-9]+}', 'QuoteController@delete');
    });
});

/*
|--------------------------------------------------------------------------
| Subscriptions
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'stripe'], function () use ($router) {
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        $router->post('subscribe', 'StripeController@subscribe');
    });

    $router->get('estimate/{stripe_plan}', 'StripeController@estimate');
    $router->post('webhook', 'StripeController@handleWebhook');
});


/*
|--------------------------------------------------------------------------
| Levels
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'levels'], function () use ($router) {
    $router->get('', 'LevelController@index');
    $router->get('{page:[0-9]+}/{perPage:[0-9]+}', 'LevelController@index');
});


/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
*/
$router->group(['prefix' => 'reports'], function () use ($router) {
    $router->get('', 'ReportController@index');
    $router->get('{page:[0-9]+}/{perPage:[0-9]+}', 'ReportController@index');
    $router->post('', 'ReportController@add');
});
