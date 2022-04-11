<?php

//Подключение автозагрузки через Composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];

$app->get('/users/{id}', function ($request, $response, $args) {
    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, '/users/show.phtml', $params);
});

$app->get('/', function ($request, $response) {
    $response->getBody()->write("welcome to Slim!");
    return $response;
});

$app->get('/users', function ($request, $response) use ($users) {
    $term = $request->getQueryParams();
    $filteredUsers = array_filter($users, fn($user) => str_contains($user, $term['term']));
    $params = [
        'users' => $filteredUsers,
        'term' => $term['term']
    ];
    return $this->get('renderer')->render($response, '/users/index.phtml', $params);
});

$app->post('/users', function ($request, $response) {
    return $response->withStatus(302);
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    print_r($args);
    $id = $args['id'];
    $response->getBody()->write("Course id is {$id}");
    return $response;
});


$app->run();