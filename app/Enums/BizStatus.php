<?php

namespace App\Enums;


enum BizStatus: int
{
    use EnumFunctions;
    case ACTIVE = 2;
    case DEF = 6;
    case DELETED = 4;

    public static function getByValue($value)
    {
        return match ($value) {
            BizStatus::ACTIVE->value => BizStatus::ACTIVE,
            BizStatus::DEF->value => BizStatus::DEF,
            BizStatus::DELETED->value =>  BizStatus::DELETED,
        };
    }

    public static function getLabel(self $value): string
    {
        return match ($value) {
            BizStatus::ACTIVE => 'active',
            BizStatus::DEF => 'pending',
            BizStatus::DELETED =>  'disabled',
        };
    }
}
