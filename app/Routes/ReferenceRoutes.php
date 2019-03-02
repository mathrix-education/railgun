<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->get("references[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ReferenceController@index");

$router->group(["prefix" => "references"], function () use ($router) {
    $router->get("{key:[1-9]\d*}", "ReferenceController@get");
    $router->get("query/{query}[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ReferenceController@query");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "ReferenceController@add");
        $router->patch("{id:[1-9]\d*}", "ReferenceController@edit");
        $router->delete("{id:[1-9]\d*}", "ReferenceController@delete");
    });
});
