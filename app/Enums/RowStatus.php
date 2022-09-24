<?php
namespace App\Enums;

enum RowStatus: int
{
    use EnumFunctions;
    
    case NORMAL = 2;
    case DELETED = 4;

    public static function containValue($value) {
        return match ($value) {
            RowStatus::NORMAL->value => true,
            RowStatus::DELETED->value => true,
            default => false
        };
    }
}