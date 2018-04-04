<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["prefix" => "money"], function () use ($router) {
    $router->get("7twanud8v86umjhp8ujhr4y1nv1z7jh60gn96s9q", "MoneyController@last");
});
