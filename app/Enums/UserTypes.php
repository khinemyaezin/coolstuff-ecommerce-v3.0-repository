<?php

namespace App\Enums;

enum UserTypes: string
{
    use EnumFunctions;

    public const CLASS_MAP = [
        'server_admin' => User::class,
        'brand_owner' => Seller::class,
        'staff' => Seller::class,
        'user' => Customers::class
    ];

    case SERVER_ADMIN = 'server_admin';
    case BRAND_OWNER = 'brand_owner';
    case USER = 'user';
    case STAFF = 'staff';

    public static function getByValue($value)
    {
        return match ($value) {
            UserTypes::SERVER_ADMIN->value => UserTypes::SERVER_ADMIN,
            UserTypes::BRAND_OWNER->value => UserTypes::BRAND_OWNER,
            UserTypes::USER->value =>  UserTypes::USER,
            UserTypes::STAFF->value =>  UserTypes::STAFF,
        };
    }
}
