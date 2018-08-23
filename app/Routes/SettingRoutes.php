<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "settings"], function () use ($router) {
    $router->get("", "SettingController@all");
    $router->get("{key:[a-z0-9\-\_]+}", "SettingController@read");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("", "SettingController@write");
        $router->delete("{key:[a-z0-9\-\_]+}", "SettingController@forgot");
    });
});
