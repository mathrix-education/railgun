<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->get("countries[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "CountryController@index");

$router->group(["prefix" => "countries"], function () use ($router) {
    $router->get("{key:[a-z\-]+}[/{value:[a-z0-9\-]+}]", "CountryController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "CountryController@add");
        $router->patch("{id:[1-9]\d*}", "CountryController@edit");
        $router->delete("{id:[1-9]\d*}", "CountryController@delete");
    });
});
