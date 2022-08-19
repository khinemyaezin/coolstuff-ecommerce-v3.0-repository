<?php

namespace App\Models;

use App\Casts\ImageUrlGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class ProdVariants extends Model
{
    use HasFactory;
    public $prod_attributes = [];

    protected $fillable = [
        "id",
        "biz_status",
        "seller_sku",
        "fk_varopt_1_hdr_id",
        "fk_varopt_1_dtl_id",
        "var_1_title",
        "fk_varopt_2_hdr_id",
        "fk_varopt_2_dtl_id",
        "var_2_title",
        "fk_varopt_3_hdr_id",
        "fk_varopt_3_dtl_id",
        "var_3_title",
        "buy_price",
        "fk_buy_currency_id",
        "selling_price",
        "qty",
        "fk_condition_id",
        "condition_desc",
        "features",
        "prod_desc",
        'start_at',
        'expired_at',
        'media_1_image',
        'media_2_image',
        'media_3_image',
        'media_4_image',
        'media_5_image',
        'media_6_image',
        'media_7_image',
        'media_8_video',
        'media_9_video',
    ];
    protected $casts = [
        'id' => 'string',
        'media_1_image' => ImageUrlGenerate::class,
        'media_2_image' => ImageUrlGenerate::class,
        'media_3_image' => ImageUrlGenerate::class,
        'media_4_image' => ImageUrlGenerate::class,
        'media_5_image' => ImageUrlGenerate::class,
        'media_6_image' => ImageUrlGenerate::class,
        'media_7_image' => ImageUrlGenerate::class,
        'media_8_video' => ImageUrlGenerate::class,
        'media_9_video' => ImageUrlGenerate::class,
        'buy_price' => 'float',
        'selling_price' => 'float',
        'features' => 'array',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
        'start_at' => 'datetime:d-m-Y h:i:s A',
        'expired_at' => 'datetime:d-m-Y h:i:s A',
    ];
    protected $hidden = [
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'fk_prod_id', 'id');
    }
    public function variantOption1Hdr()
    {
        return $this->hasOne(VariantOptionHdrs::class, 'id', 'fk_varopt_1_hdr_id');
    }
    public function variantOption1Dtl()
    {
        return $this->hasOne(VariantOptionDtls::class, 'id', 'fk_varopt_1_dtr_id');
    }
    public function variantOption2Hdr()
    {
        return $this->hasOne(VariantOptionHdrs::class, 'id', 'fk_varopt_2_hdr_id');
    }
    public function variantOption2Dtl()
    {
        return $this->hasOne(VariantOptionDtls::class, 'id', 'fk_varopt_2_dtr_id');
    }
    public function variantOption3Hdr()
    {
        return $this->hasOne(VariantOptionHdrs::class, 'id', 'fk_varopt_3_hdr_id');
    }
    public function variantOption3Dtl()
    {
        return $this->hasOne(VariantOptionDtls::class, 'id', 'fk_varopt_3_dtr_id');
    }
    public function attributes()
    {
        return $this->belongsToMany(VariantOptionHdrs::class, 'prod_attributes', 'fk_variant_id', 'fk_varopt_hdr_id');
    }
    public function condition()
    {
        return $this->hasOne(Conditions::class,'id','fk_condition_id');
    }
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($variant) {
            error_log("deleting variant");
            $variant->attributes()->detach();
        });
        self::deleted(function (ProdVariants $variant) {
            $images = [
                'media_1_image',
                'media_2_image',
                'media_3_image',
                'media_4_image',
                'media_5_image',
                'media_6_image',
                'media_7_image',
                'media_8_video',
                'media_9_video',
            ];
            foreach ($images as $image) {
                $path = $variant->getRawOriginal($image);
                error_log('updated event -> ' . $path);
                $file = public_path('storage/' .  $path);
                error_log('Image.deleting.$file : ' . $file);
                if (File::exists($file)) {
                    error_log('Image.deleting.$file->exists : true');
                    File::delete($file);
                } else {
                    error_log('Image.deleting.$file->not exists : true');
                }
            }
           
        });
    }
}
