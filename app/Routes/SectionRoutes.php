<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "sections", "middleware" => "auth:api"], function () use ($router) {
    $router->get("", "SectionController@index");
    $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "SectionController@index");
    $router->get("{id:[0-9]+}", "SectionController@get");
    $router->post("", "SectionController@add");
    $router->patch("{id:[0-9]+}", "SectionController@edit");
    $router->delete("{id:[0-9]+}", "SectionController@delete");
});
