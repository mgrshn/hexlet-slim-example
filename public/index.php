<?php

//Подключение автозагрузки через Composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    $response->getBody()->write("welcome to Slim!");
    return $response;
});

$app->get('/users', function ($request, $response) {
    $response->getBody()->write('GET /users');
    return $response;
});

$app->post('/users', function ($request, $response) {
    return $response->withStatus(302);
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    $response->getBody()->write("Course id is {$id}");
    return $response;
});


$app->run();