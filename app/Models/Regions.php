<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regions extends Model
{
    use HasFactory;
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];
    protected $hidden = [
        'status',
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        'id',
        'status',
        'biz_status',
        'country_name',
        'country_code',
        'currency_code'
    ];
}
