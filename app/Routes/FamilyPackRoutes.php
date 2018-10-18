<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api", "prefix" => "family-packs"], function () use ($router) {
    $router->get("{key:[1-9]\d*}", "FamilyPackController@get");
    $router->post("{familyPackId:[1-9]\d*}/children", "FamilyPackController@addChild");
    $router->delete("{familyPackId:[1-9]\d*}/children", "FamilyPackController@removeChild");
});
