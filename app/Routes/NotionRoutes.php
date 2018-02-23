<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->get("notions[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "NotionController@index");

$router->group(["prefix" => "notions"], function () use ($router) {
    $router->get("[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "NotionController@index");
    $router->get("by-{what:[a-z\-]+}/{id:[1-9]\d*}", "NotionController@by");
    $router->get("{key:[1-9]\d*}", "NotionController@get");
    $router->get("{key:[a-z\-]+}/{value:[a-z0-9\-]+}", "NotionController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "NotionController@add");
        $router->patch("{id:[1-9]\d*}", "NotionController@edit");
        $router->delete("{id:[1-9]\d*}", "NotionController@delete");
    });

});
