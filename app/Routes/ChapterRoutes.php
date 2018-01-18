<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("chapters[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ChapterController@index");
});

$router->group(["prefix" => "chapters"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "ChapterController@add");
        $router->patch("{id:[1-9]\d*}", "ChapterController@edit");
        $router->delete("{id:[1-9]\d*}", "ChapterController@delete");
    });

    $router->get("by-class-discipline/{class_id:[1-9]\d*}/{discipline_id:[1-9]\d*}", "ChapterController@by_class_discipline");
    $router->get("by-{what:[a-z\-]+}/{id:[1-9]\d*}", "ChapterController@by");
    $router->get("{key:[1-9]\d*}", "ChapterController@get");
    $router->get("{key:[a-z\-]+}/{value:[a-z0-9\-]+}", "ChapterController@get");
});
