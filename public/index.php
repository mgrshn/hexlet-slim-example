<?php

//Подключение автозагрузки через Composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use App\Storage;
use App\IdGenerator;

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$usersStorage = new Storage();

function isStorageEmpty(): bool
{
    return file_get_contents(__DIR__ . '/../storage/users.json') == false ? 1 : 0;
}

$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => ['name' => '', 'email' => ''],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, '/users/new.phtml', $params);
});

$app->post('/users', function ($request, $response) use ($usersStorage) {
    $user = $request->getParsedBody('user')['user'];
    var_dump($user);
    $id = IdGenerator::generateId();
    $user['id'] = $id;
    $usersStorage->addUser($user);

    return $response->withStatus(302)->withHeader('Location', '#');
});

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

$app->get('/users', function ($request, $response) use ($usersStorage) {
    $term = $request->getQueryParams();
    $users = $usersStorage->getUsers();
    $filteredUsers = array_filter($users, fn($user) => str_contains($user['name'], $term['term']));
    $params = [
        'users' => $filteredUsers,
        'term' => $term['term']
    ];
    return $this->get('renderer')->render($response, '/users/index.phtml', $params);
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    print_r($args);
    $id = $args['id'];
    $response->getBody()->write("Course id is {$id}");
    return $response;
});

$app->run();