<?php

/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "plans"], function () use ($router) {
    $router->get("", "PlanController@all");
    $router->get("{planId}", "PlanController@find");
});
