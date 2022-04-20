<?php

//Подключение автозагрузки через Composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;
use App\Storage;
use App\IdGenerator;
use App\Validator;

session_start();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function() {
    return new \Slim\Flash\Messages();
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);
$app->add(MethodOverrideMiddleware::class);

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
})->setName('createNewUser');

$app->post('/users', function ($request, $response) use ($usersStorage) {

    $user = $request->getParsedBody('user')['user'];
    $errors = Validator::validate($user);
    if (count($errors) === 0) {
        $id = IdGenerator::generateId();
        $user['id'] = $id;
        $usersStorage->addUser($user);
        $this->get('flash')->addMessage('success', 'User added');
    
        return $response->withStatus(302)->withHeader('Location', '/users');
    }

    $params = [
        'errors' => $errors,
        'user' => $user
    ];
    return $this->get('renderer')->render($response->withStatus(422), '/users/new.phtml', $params);
})->setName('toUsersAfterCreate');

$app->get('/users/{id}', function ($request, $response, $args) use ($usersStorage) {
    $users = $usersStorage->getUsers();
    // Валидируем idшник в URI
    foreach ($users as $user) {
        $ids[] = array_key_exists('id', $user) ? $user['id'] : false;
    }
    if (!in_array((int) $args['id'], $ids, true)) {
        $response->getBody()->write('This user not created yet :(');
        return $response->withStatus(404);
    }

    //Получаем имя текущего пользователя.
    foreach ($users as $user) {
        if ((int) $args['id'] === $user['id']) {
            $currentUserName = $user['name'];
        } else {
            continue;
        } 
    }
    
    $flash = $this->get('flash')->getMessages();
    $params = [
    'id' => $args['id'],
    'nickname' => $currentUserName,
    'flash' => $flash
    ];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, '/users/show.phtml', $params);
})->setName('user');

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) use ($router) {

    $response->getBody()->write("welcome to Slim!");
    return $response;
})->setName('startPage');

$app->get('/users', function ($request, $response) use ($usersStorage) {
    $term = $request->getQueryParams();
    $users = $usersStorage->getUsers();
    $filteredUsers = array_filter($users, fn($user) => /*str_contains*/str_starts_with(strtolower($user['name']), strtolower($term['term'])));
    $messages = $this->get('flash')->getMessages();

    $params = [
        'users' => $filteredUsers,
        'term' => $term['term'],
        'flash' => $messages
    ];
    return $this->get('renderer')->render($response, '/users/index.phtml', $params);
})->setName('getAllUsers');

$app->get('/courses/{id}', function ($request, $response, array $args) {
    print_r($args);
    $id = $args['id'];
    $response->getBody()->write("Course id is {$id}");
    return $response;
})->setName('courses');

$app->get('/users/{id}/edit', function ($request, $response, array $args) use ($usersStorage) {
    $id = (int) $args['id'];
    $users = $usersStorage->getUsers();
    if ($id > count($users)) {
        $response->getBody()->write('This user not created yet :(');
        return $response->withStatus(404);
    }
    foreach ($users as $userProps) {
        if ($userProps['id'] === $id) {
            $user = $userProps;
        } else {
            continue;
        }
    }
    $params = [
        'user' => $user,
        'errors' => []
    ];

    return $this->get('renderer')->render($response, '/users/edit.phtml', $params);
})->setName('editUser');

$app->patch('/users/{id}', function ($request, $response, array $args) use ($usersStorage, $router) {
    $id = (int) $args['id'];
    $users = $usersStorage->getUsers();
    if ($id > count($users)) {
        $response->getBody()->write('This user not created yet :(');
        return $response->withStatus(404);
    }
    $newUserProps = $request->getParsedBody('user')['user'];
    $newUserProps['id'] = $id;
    $errors = Validator::validate($newUserProps);
    if (count($errors) === 0) {
        $flash = $this->get('flash')->addMessage('success', 'User has been changed');
        $usersStorage->changeUser($newUserProps);
        $url = $router->urlFor('user', ['id' => $id]);
        return $response->withStatus(302)->withHeader('Location', $url);
    }
    

    $params = [
        'user' => $newUserProps,
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response->withStatus(422), '/users/edit.phtml', $params);
});

$app->run();