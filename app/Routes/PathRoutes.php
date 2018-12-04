<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "paths", "middleware" => "auth:api"], function () use ($router) {
    $router->post("index", "PathController@markIndex");
});
