<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryLeaves extends CsModel
{
    use HasFactory;
    protected  $casts = [
        'id' => 'string',
    ];
    protected $hidden = [
        'status'
    ];
}
