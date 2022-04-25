<?php

namespace App;

class IdGenerator
{
    public static function generateId(): int 
    {
        $users = json_decode(file_get_contents(__DIR__ . '/../storage/users.json'), true);
        return isset($users) ? count($users) + 1 : 1;
    }
}
