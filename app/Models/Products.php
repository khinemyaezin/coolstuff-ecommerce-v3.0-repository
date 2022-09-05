<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];
    protected $hidden = [
        'status'
    ];
    protected $fillable = [
        'id',
        "biz_status",
        "title",
        "brand",
        "manufacture",
        "package_qty",
        "fk_brand_id",
        "fk_category_id",
        "fk_packtype_id",
        "fk_group_id",
        "fk_currency_id",
        'fk_varopt_1_hdr_id',
        'fk_varopt_2_hdr_id',
        'fk_varopt_3_hdr_id',
    ];
    public function myBrand()
    {
        return $this->hasOne(Brands::class,'id','fk_brand_id');
    }
    public function category()
    {
        return $this->hasOne(CategoryLeaves::class,'id','fk_category_id');
    }
    public function packType()
    {
        return $this->hasOne(PackTypes::class,'id','fk_packtype_id');
    }
    public function currency()
    {
        return $this->hasOne(Regions::class,  'id','fk_currency_id');
    }
    public function variants($relations = [])
    {
        return $this->hasMany(ProdVariants::class,'fk_prod_id','id')->with($relations);
    }
    public function variantOption1Hdr()
    {
        return $this->hasOne(VariantOptionHdrs::class,'id','fk_varopt_1_hdr_id');
    }
    public function variantOption2Hdr()
    {
        return $this->hasOne(VariantOptionHdrs::class,'id','fk_varopt_2_hdr_id');
    }
    public function variantOption3Hdr()
    {
        return $this->hasOne(VariantOptionHdrs::class,'id','fk_varopt_3_hdr_id');
    }

    public static function boot()
    {
        parent::boot();
    }

}
