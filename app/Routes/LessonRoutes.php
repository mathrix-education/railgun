<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("lessons[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "LessonController@index");

    $router->group(["prefix" => "lessons"], function () use ($router) {
        $router->get("{id:[1-9]\d*}", "LessonController@get");
        $router->post("", "LessonController@add");
        $router->patch("{id:[1-9]\d*}", "LessonController@edit");
        $router->delete("{id:[1-9]\d*}", "LessonController@delete");
    });
});
