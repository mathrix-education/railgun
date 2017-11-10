<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "courses"], function () use ($router) {
    $router->get("", "CourseController@index");
    $router->get("{id:[0-9]+}", "CourseController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "CourseController@add");
        $router->patch("{id:[0-9]+}", "CourseController@edit");
        $router->delete("{id:[0-9]+}", "CourseController@delete");
    });
});
