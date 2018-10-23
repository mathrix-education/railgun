<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api", "prefix" => "files"], function () use ($router) {
    $router->get("[{directory:[a-zA-Z0-9\_\-\/\.]+}]", "FileController@explore");
});
