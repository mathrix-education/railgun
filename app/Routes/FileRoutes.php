<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api", "prefix" => "files"], function () use ($router) {
    $router->get("[{directory}]", "FileController@directory");
});
