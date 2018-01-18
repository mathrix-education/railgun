<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("users[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "UserController@index");
});

$router->group(["prefix" => "users"], function () use ($router) {
    $router->post("register", "UserController@register");
    $router->post("login", "AuthController@login");
    $router->post("refresh", "AuthController@refresh");
    $router->post("forgot", "AuthController@forgot");
    $router->post("reset/{token}", "AuthController@reset");
    $router->get("exists/{field}/{value}", "UserController@exists");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->get("[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "UserController@index");
        $router->get("profile", "UserController@profile");
        $router->get("{key:[1-9]\d*}", "UserController@get");
        $router->get("{key:[a-z\-]+}/{value:[a-z0-9\-]+}", "UserController@get");

        $router->patch("{id:[1-9]\d*}", "UserController@edit");
        $router->delete("{id:[1-9]\d*}", "UserController@delete");
        $router->post("search[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "UserController@search");

        $router->post("points", "UserController@points");
        $router->post("points/{id:[1-9]\d*}", "UserController@points");
    });
});
