<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantOptionHdrs extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'title'
    ];
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];
    protected $touches = ['prodVariants'];


    public function optionDetails()
    {
        return  $this->hasMany(VariantOptionDtls::class, 'fk_varopt_hdr_id', 'id');
    }
    public function optionUnits()
    {
        return  $this->hasMany(VariantOptionUnits::class, 'fk_varopt_hdr_id', 'id');
    }
    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'category_attributes',  'fk_varoption_hdr_id','fk_category_id');
    }
    public function prodVariants()
    {
        return $this->belongsToMany(ProdVariants::class,'prod_attributes', 'fk_varopt_hdr_id','fk_variant_id');
    }
}
