<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conditions extends Model
{
    use HasFactory,CsModel;
    protected  $casts = [
        'id' => 'string',
    ];
    protected $fillable = [
        'id',
        'status',
        'biz_status',
        'title'
    ];
    protected $hidden = [
        'status'
    ];
}
