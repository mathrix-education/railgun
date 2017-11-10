<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "levels"], function () use ($router) {
    $router->get("", "LevelController@index");
    $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "LevelController@index");
});