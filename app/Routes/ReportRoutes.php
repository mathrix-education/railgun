<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "reports"], function () use ($router) {
    $router->get("", "ReportController@index");
    $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "ReportController@index");
    $router->post("", "ReportController@add");
});