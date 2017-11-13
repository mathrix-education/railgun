<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "users"], function () use ($router) {
    $router->post("register", "UserController@register");
    $router->post("login", "AuthController@login");
    $router->post("refresh", "AuthController@refresh");
    $router->post("forgot", "AuthController@forgot");
    $router->post("reset/{token}", "AuthController@reset");
    $router->get("exists/{field}/{value}", "UserController@exists");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->get("", "UserController@index");
        $router->get("me", "UserController@me");

        $router->get("{page:[0-9]+}/{perPage:[0-9]+}", "UserController@index");
        $router->get("{id:[0-9]+}", "UserController@get");
        $router->patch("{id:[0-9]+}", "UserController@edit");
        $router->delete("{id:[0-9]+}", "UserController@delete");
        $router->post("search", "UserController@search");

        $router->post("points", "UserController@points");
        $router->post("points/{id:[0-9]+}", "UserController@points");
    });
});
