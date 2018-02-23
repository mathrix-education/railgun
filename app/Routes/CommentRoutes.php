<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("comments[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "CommentController@index");
});

$router->group(["prefix" => "comments"], function () use ($router) {

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->get("{key:[1-9]\d*}", "CommentController@get");
        $router->get("latest", "CommentController@latest");
        $router->get("latest/{page:[1-9]\d*}/{perPage:[1-9]\d*}", "CommentController@latest");
        $router->post("", "CommentController@add");
        $router->patch("{id:[1-9]\d*}", "CommentController@edit");
        $router->delete("{id:[1-9]\d*}", "CommentController@delete");
        $router->post("search", "CommentController@search");
    });

    $router->get("by-{what:[a-z\-]+}/{id:[1-9]\d*}[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "CommentController@by");

});
