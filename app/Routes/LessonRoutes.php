<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "lessons"], function () use ($router) {
    $router->get("", "LessonController@index");
    $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "LessonController@index");
    $router->get("{id:[0-9]+}", "LessonController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "LessonController@add");
        $router->patch("{id:[0-9]+}", "LessonController@edit");
        $router->delete("{id:[0-9]+}", "LessonController@delete");
    });
});
