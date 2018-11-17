<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "csv"], function () use ($router) {
    $router->get("4ba09ec5da8ee809f3c470da188d9335", "CSVController@routes1Module");
});
