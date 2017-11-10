<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "notions"], function () use ($router) {
    $router->get("", "NotionController@index");
    $router->get("{id:[0-9]+}", "NotionController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "NotionController@add");
        $router->patch("{id:[0-9]+}", "NotionController@edit");
        $router->delete("{id:[0-9]+}", "NotionController@delete");
    });
});
