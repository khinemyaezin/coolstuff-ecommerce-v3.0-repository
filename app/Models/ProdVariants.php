<?php

namespace App\Models;

use App\Casts\ImageUrlGenerate;
use App\Services\Utility;
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
        return $this->hasOne(VariantOptionDtls::class, 'id', 'fk_varopt_1_dtl_id');
    }
    public function variantOption2Hdr()
    {
        return $this->hasOne(VariantOptionHdrs::class, 'id', 'fk_varopt_2_hdr_id');
    }
    public function variantOption2Dtl()
    {
        return $this->hasOne(VariantOptionDtls::class, 'id', 'fk_varopt_2_dtl_id');
    }
    public function variantOption3Hdr()
    {
        return $this->hasOne(VariantOptionHdrs::class, 'id', 'fk_varopt_3_hdr_id');
    }
    public function variantOption3Dtl()
    {
        return $this->hasOne(VariantOptionDtls::class, 'id', 'fk_varopt_3_dtl_id');
    }
    public function attributes()
    {
        return $this->belongsToMany(VariantOptionHdrs::class, 'prod_attributes', 'fk_variant_id', 'fk_varopt_hdr_id');
            
    }
    public function condition()
    {
        return $this->hasOne(Conditions::class, 'id', 'fk_condition_id');
    }
    public function media_1_image()
    {
        return $this->hasOne(CsFile::class, 'id', 'media_1_image');
    }
    public function media_2_image()
    {
        return $this->hasOne(CsFile::class, 'id', 'media_2_image');
    }
    public function media_3_image()
    {
        return $this->hasOne(CsFile::class, 'id', 'media_3_image');
    }
    public function media_4_image()
    {
        return $this->hasOne(CsFile::class, 'id', 'media_4_image');
    }
    public function media_5_image()
    {
        return $this->hasOne(CsFile::class, 'id', 'media_5_image');
    }
    public function media_6_image()
    {
        return $this->hasOne(CsFile::class, 'id', 'media_6_image');
    }
    public function media_7_image()
    {
        return $this->hasOne(CsFile::class, 'id', 'media_7_image');
    }
    public function media_8_video()
    {
        return $this->hasOne(CsFile::class, 'id', 'media_8_video');
    }
    public function media_9_video()
    {
        return $this->hasOne(CsFile::class, 'id', 'media_9_video');
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($variant) {
            Utility::log('variant [' . $variant->id . '] deleted');
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
