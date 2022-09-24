<?php
namespace App\Enums;

trait EnumFunctions {
    public static function all()
    {
        return array_map(function($c)
        {
            return $c->value;
        },self::cases());
    }
}