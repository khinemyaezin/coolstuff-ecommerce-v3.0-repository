<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brands extends Model
{
    use HasFactory,CsModel;
    
    protected $fillable = [
        'title',
        'public_id',
        'fk_region_id',
        'profile_image',
        'cover_image',
        "def_currency_id",
        "industry_id",
        "phone",
        "sys_email",
        "cus_email",
        'description'
    ];
    protected  $casts = [
        'id' => 'string',
    ];

    protected $hidden = [
        'status'
    ];


    public function users()
    {
        return $this->hasMany(BrandOwners::class, 'fk_brand_id', 'id');
    }
    public function region()
    {
        return $this->hasOne(Regions::class, 'id', 'fk_region_id');
    }
    public function defaultCurrency()
    {
        return $this->hasOne(Regions::class,  'id', 'def_currency_id');
    }
    public function profileImage()
    {
        return $this->hasOne(CsFile::class, 'id', 'profile_image');
    }
    public function coverImage()
    {
        return $this->hasOne(CsFile::class, 'id', 'cover_image');
    }
    public function files()
    {
        return $this->belongsToMany(CsFile::class, 'files_in_brands', 'fk_brand_id', 'fk_file_id');
    }
    public function locations()
    {
        return $this->hasMany(Location::class, 'fk_brand_id', 'id');
    }

    public function industry()
    {
        return $this->hasOne(Categories::class,  'id', 'industry_id');
    }
}
