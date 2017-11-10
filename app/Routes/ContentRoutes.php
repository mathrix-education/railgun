<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "content"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->get("upload", "ContentController@upload");
    });
    $router->get("tree", "ContentController@tree");

    $router->get("countries", "ContentController@countries");
    $router->get("program/{classId:[0-9]+}", "ContentController@program");
    $router->get("modules/{activityId:[0-9]+}", "ContentController@modules");
    $router->get("search/{query}", "ContentController@searchGlobal");
    $router->get("search/{query}/{page:[0-9]+}/{perPage:[0-9]+}", "ContentController@searchGlobal");
});
