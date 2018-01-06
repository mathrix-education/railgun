<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("chapters[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ChapterController@index");
});

$router->group(["prefix" => "chapters"], function () use ($router) {
    $router->get("{id:[1-9]\d*}", "ChapterController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "ChapterController@add");
        $router->patch("{id:[1-9]\d*}", "ChapterController@edit");
        $router->delete("{id:[1-9]\d*}", "ChapterController@delete");
    });
});
