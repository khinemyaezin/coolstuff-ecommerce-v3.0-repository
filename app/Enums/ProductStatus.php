<?php
namespace App\Enums;

enum ProductStatus:string {
    use EnumFunctions;

    CASE ACTIVE  = 'active';
    CASE WAITING = 'waiting';
    CASE EXPIRED = 'expired';
    CASE OUTOFSTOCK = 'outofstock';
    CASE PENDING = 'pending';
    CASE DISABLED = 'disabled';
}