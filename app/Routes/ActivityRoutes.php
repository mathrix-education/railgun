<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "activities"], function () use ($router) {
    $router->get("{id:[0-9]+}", "ActivityController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->get("", "ActivityController@index");
        $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "ActivityController@index");
        $router->post("", "ActivityController@add");
        $router->patch("{id:[0-9]+}", "ActivityController@edit");
        $router->delete("{id:[0-9]+}", "ActivityController@delete");
        $router->post("search", "ActivityController@search");

        $router->get("favorites", "ActivityController@favorites");
        $router->get("to-revise", "ActivityController@to_revise");
        $router->patch("{id:[0-9]+}/mark", "ActivityController@mark");
    });
});