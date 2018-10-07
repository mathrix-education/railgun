<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "scores"], function () use ($router) {
    $router->get("{game:[a-z\-]+}/top[/{interval:[a-z]+}[/{count:[0-9]+}]]", "ScoreController@top");
    $router->get("{game:[a-z\-]+}/around/{score:[0-9]+}[/{delta:[0-9]+}]", "ScoreController@around");
    $router->get("{game:[a-z\-]+}/personal-best", "ScoreController@personalBest");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->get("{game:[a-z\-]+}/token", "ScoreController@token");
        $router->post("{game:[a-z\-]+}", "ScoreController@register");
    });
});
