<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory,CsModel;
    protected $fillable = [
        'id',
        'title'
     ];
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];
    protected $hidden = [
        'status',
        'pivot',
        'ts_path_search'
    ];

    public function attributes()
    {
         return $this->belongsToMany(VariantOptionHdrs::class, 'category_attributes', 'fk_category_id', 'fk_varoption_hdr_id');
    }
}
