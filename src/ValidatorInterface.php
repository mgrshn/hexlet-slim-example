<?php

namespace App;

interface ValidatorInterface 
{
    // Return array of errors, or empty erray
    public static function validate(array $data);
}