<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "countries"], function () use ($router) {
    $router->get("", "CountryController@index");
    $router->get("{id:[0-9]+}", "CountryController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "CountryController@add");
        $router->patch("{id:[0-9]+}", "CountryController@edit");
        $router->delete("{id:[0-9]+}", "CountryController@delete");
    });
});
