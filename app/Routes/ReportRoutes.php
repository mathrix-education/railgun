<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("reports[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "ReportController@index");
});

$router->group(["prefix" => "reports"], function () use ($router) {
    $router->post("", "ReportController@add");
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->get("{key:[1-9]\d*}", "ReportController@get");
        $router->patch("{id:[1-9]\d*}", "ReportController@edit");
        $router->delete("{id:[1-9]\d*}", "ReportController@delete");
    });
});
