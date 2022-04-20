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


    public function changeUser(array $newUser)
    {
        $users = $this->getUsers();
        $user = $users['user'. (string) $newUser['id']];
        if (isset($newUser['name']) && $newUser['name'] != '') {
            $user['name'] = $newUser['name'];
        }
        if (isset($newUser['email']) && $newUser['email'] != '') {
            $user['email'] = $newUser['email'];
        }
        $users['user'. (string) $newUser['id']] = $user;
        print_r($user);
        $this->storageData = $users;
        file_put_contents(__DIR__ . '/../storage/users.json', json_encode($this->storageData));
        //write logic here  
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


/*$storage = new Storage();
$users = $storage->getUsers();
//print_r($users);
$user = $users['user1'];
print_r($storage->changeUser($user));*/