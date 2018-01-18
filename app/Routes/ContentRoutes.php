<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "content"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("upload", "ContentController@upload");
    });
    $router->get("tree", "ContentController@tree");
});

$router->get("search/{query}[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ContentController@searchGlobal");
