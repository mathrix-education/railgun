<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "chapters"], function () use ($router) {
    $router->get("{id:[0-9]+}", "ChapterController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->get("", "ChapterController@index");
        $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "ChapterController@index");
        $router->post("", "ChapterController@add");
        $router->patch("{id:[0-9]+}", "ChapterController@edit");
        $router->delete("{id:[0-9]+}", "ChapterController@delete");
    });
});