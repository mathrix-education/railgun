<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "content"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("upload", "ContentController@upload");
    });
    $router->get("tree", "ContentController@tree");

    $router->get("countries", "ContentController@countries");
    $router->get("program/{classId:[1-9]\d*}", "ContentController@program");
    $router->get("modules/{activityId:[1-9]\d*}", "ContentController@modules");
    $router->get("search/{query}[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ContentController@searchGlobal");
});
