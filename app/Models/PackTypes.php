<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackTypes extends Model
{
    use HasFactory;
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
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
