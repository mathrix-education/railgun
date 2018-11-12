<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "content"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("upload", "ContentController@upload");
    });
    $router->get("8f6dc8b113d6b0c3b65609ce11c94966", "ContentController@routes");
    $router->get("tree", "ContentController@tree");
    $router->post("flat", "ContentController@flat");
});

$router->get("search/{query}[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ContentController@searchGlobal");
