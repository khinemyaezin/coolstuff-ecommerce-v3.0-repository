<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantOptionUnits extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'code',
        'fk_varopt_hdr_id'
    ];
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];
    public function optionHeader()
    {
        return $this->belongsTo(VariantOptionHdrs::class, 'fk_varopt_hdr_id', 'id');
    }
}
