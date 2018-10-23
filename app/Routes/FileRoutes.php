<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api", "prefix" => "files"], function () use ($router) {
    $router->post("", "FileController@upload");
    $router->get("explore/[{directory:[a-zA-Z0-9\_\-\/\.]+}]", "FileController@explore");
    $router->get("[{path:[a-zA-Z0-9\_\-\/\.]+}]", "FileController@info");
    $router->patch("[{path:[a-zA-Z0-9\_\-\/\.]+}]", "FileController@rename");
    $router->delete("[{path:[a-zA-Z0-9\_\-\/\.]+}]", "FileController@delete");
});
