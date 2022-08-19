<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdAttributes extends Model
{
    use HasFactory;
    protected $fillable = [ 
        'id',
        'fk_varopt_hdr_id',
        'fk_varopt_dtl_id',
        'fk_varopt_unit_id',
        'value',
    ];
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];
    protected $hidden = [
        'status'
    ];
    
}
