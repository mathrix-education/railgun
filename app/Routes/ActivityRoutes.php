<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("activities[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ActivityController@index");
});

$router->group(["prefix" => "activities"], function () use ($router) {
    $router->get("{id:[1-9]\d*}", "ActivityController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "ActivityController@add");
        $router->patch("{id:[1-9]\d*}", "ActivityController@edit");
        $router->delete("{id:[1-9]\d*}", "ActivityController@delete");

        $router->post("search", "ActivityController@search");
        $router->get("favorites", "ActivityController@favorites");
        $router->get("to-revise", "ActivityController@to_revise");
        $router->patch("{id:[1-9]\d*}/mark", "ActivityController@mark");
    });
});
