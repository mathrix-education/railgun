<?php
/** @var Laravel\Lumen\Routing\Router $router */
$router->group(["middleware" => "auth:api"], function () use ($router) {
    $router->get("comments[/{page:[1-9]\d*}/{perPage:[1-9]\d*}]", "CommentController@index");
});

$router->group(["prefix" => "comments"], function () use ($router) {
    $router->get("{id:[1-9]\d*}", "CommentController@get");

    $router->group(["middleware" => "auth:api"], function () use ($router) {
        $router->get("latest", "CommentController@latest");
        $router->get("latest/{page:[1-9]\d*}/{perPage:[1-9]\d*}", "CommentController@latest");
        $router->post("", "CommentController@add");
        $router->patch("{id:[1-9]\d*}", "CommentController@edit");
        $router->delete("{id:[1-9]\d*}", "CommentController@delete");
        $router->post("search", "CommentController@search");
    });

    $router->get("{commentable_type}/{commentable_id:[1-9]\d*}", "CommentController@type");
    $router->get("{commentable_type}/{commentable_id:[1-9]\d*}/{page:[1-9]\d*}/{perPage:[1-9]\d*}",
        "CommentController@type");
});
