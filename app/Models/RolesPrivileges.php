<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolesPrivileges extends Model
{
    use HasFactory;
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];
    protected $hidden = [
        'id',
        'status'
    ];
}
