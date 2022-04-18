<?php

namespace App;

Class Validator implements ValidatorInterface 
{
    public static function validate(array $user): array
    {
        $errors = [];
        if ($user['name'] === '' || empty($user['name'])) {
            $errors['name'] = 'Enter your name';
        }
        if ($user['email'] === '' || empty($user['email'])) {
            $errors['email'] = 'Enter your email';
        }

        return $errors;
    }
}