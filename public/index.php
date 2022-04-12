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

$app->post('/users', function ($request, $response) {
    $user = $request->getParsedBody('user');
    $id = rand(1, 1000);
    $user['id'] = $id;
    //проверка на пустоту в storage
    if (isStorageEmpty()) {
        file_put_contents(
        __DIR__ . '/../storage/users.json',
        json_encode(array())
        );
    }

    $allUsers = json_decode(file_get_contents(
        __DIR__ . '/../storage/users.json'
    ), true);
    $allUsers[] = $user;
    file_put_contents(
        __DIR__ . '/../storage/users.json',
        json_encode($allUsers),
    );

    return $response->withStatus(302)->withHeader('Location', '/users');
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

$app->get('/users', function ($request, $response) use ($users) {
    $term = $request->getQueryParams();
    $filteredUsers = array_filter($users, fn($user) => str_contains($user, $term['term']));
    $params = [
        'users' => $filteredUsers,
        'term' => $term['term']
    ];
    return $this->get('renderer')->render($response, '/users/index.phtml', $params);
});

/*$app->post('/users', function ($request, $response) {
    return $response->withStatus(302);
});*/

$app->get('/courses/{id}', function ($request, $response, array $args) {
    print_r($args);
    $id = $args['id'];
    $response->getBody()->write("Course id is {$id}");
    return $response;
});




$app->run();