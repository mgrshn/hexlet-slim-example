<?php

namespace App;

use App\IdGenerator;

class Storage
{
    public array $storageData;

    public function __construct()
    {
        //если файл пустой
        if (!file_get_contents(__DIR__ . '/../storage/users.json')) {
            $this->storageData = [];
        } else {
            $this->storageData = json_decode(file_get_contents(__DIR__ . '/../storage/users.json'), true);
        }
    }

    public function getUsers()
    {
        return $this->storageData;
    }

    public function addUser(array $user)
    {
        $users = $this->getUsers();
        $userId = $user['id'];
        $users['user' . $userId] = $user;//['name' => $user['name'], 'email' => $user['email'], 'id' => $user['id']];
        $this->storageData = $users;
        file_put_contents(__DIR__ . '/../storage/users.json', json_encode($this->storageData));
    }
}


