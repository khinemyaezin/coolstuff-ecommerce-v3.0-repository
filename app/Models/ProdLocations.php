<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdLocations extends Model
{
    use HasFactory;
    protected $table='prod_locations';
    protected $fillable = [
        'id',
        'status',
        'biz_status',
        'quantity'
    ];
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];
}
