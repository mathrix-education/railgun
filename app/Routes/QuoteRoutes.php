<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->get("quotes[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "QuoteController@index");

$router->group(["prefix" => "quotes"], function () use ($router) {
    $router->get("{key:[1-9]\d*}", "QuoteController@get");
    $router->get("random[/{count:[1-9]\d*}]", "QuoteController@random");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "QuoteController@add");
        $router->patch("{id:[1-9]\d*}", "QuoteController@edit");
        $router->delete("{id:[1-9]\d*}", "QuoteController@delete");
    });
});
