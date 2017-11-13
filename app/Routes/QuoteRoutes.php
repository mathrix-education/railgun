<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "quotes"], function () use ($router) {
    $router->get("", "QuoteController@index");
    $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "QuoteController@index");
    $router->get("{id:[0-9]+}", "QuoteController@get");
    $router->get("random", "QuoteController@random");
    $router->get("random/{count:[0-9]+}", "QuoteController@random");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "QuoteController@add");
        $router->patch("{id:[0-9]+}", "QuoteController@edit");
        $router->delete("{id:[0-9]+}", "QuoteController@delete");
    });
});