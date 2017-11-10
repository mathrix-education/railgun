<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "votes"], function () use ($router) {
    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->post("{votable_type}/{votable_id}/up", "VoteController@up");
        $router->post("{votable_type}/{votable_id}/down", "VoteController@down");
    });
});
