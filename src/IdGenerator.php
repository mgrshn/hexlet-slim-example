<?php

namespace App;

class IdGenerator
{
    public function generateId(): int 
    {
        $users = json_decode(file_get_contents(__DIR__ . '/../storage/users.json'), true);
        print_r($users);
        return isset($users) ? count($users) + 1 : 1;
    }
}

print_r(IdGenerator::generateId());