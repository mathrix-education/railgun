<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->get("modules[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ModuleController@index");

$router->group(["prefix" => "modules"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "ModuleController@add");
        $router->patch("{id:[1-9]\d*}", "ModuleController@edit");
        $router->delete("{id:[1-9]\d*}", "ModuleController@delete");

        $router->patch("{id:[1-9]\d*}/mark", "ModuleController@mark");
    });

    $router->get("by-{what:[a-z\-]+}/{id:[1-9]\d*}", "ModuleController@by");
    $router->get("{key:[a-z\-]+}[/{value:[a-z0-9\-]+}]", "ModuleController@get");
});
