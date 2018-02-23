<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("sections[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "SectionController@index");
});

$router->group(["prefix" => "sections", "middleware" => "auth:api"], function () use ($router) {
    $router->get("by-{what:[a-z\-]+}/{id:[1-9]\d*}", "SectionController@by");
    $router->get("{key:[1-9]\d*}", "SectionController@get");
    $router->get("{key:[a-z\-]+}/{value:[a-z0-9\-]+}", "SectionController@get");

    $router->post("", "SectionController@add");
    $router->patch("{id:[1-9]\d*}", "SectionController@edit");
    $router->delete("{id:[1-9]\d*}", "SectionController@delete");
});
