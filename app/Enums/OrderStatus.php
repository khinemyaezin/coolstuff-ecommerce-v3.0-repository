<?php
namespace App\Enums;

enum ProductStatus:string {
    use EnumFunctions;
    case PENDING = "pending"; // Pending

    case ON_HOLD = 'on_hold'; // Payment 
    case FAILED = 'failed'; // Payment failed (retry)

    case PROCESSING = 'processing';
    case COMPLETED = 'completed';

    case REFUNDED = 'refunded';
    case CANCELLED = 'canceled'; // Set by store owner

}