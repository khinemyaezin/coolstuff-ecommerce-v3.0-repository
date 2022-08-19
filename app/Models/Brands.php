<?php

namespace App\Models;

use App\Casts\ImageUrlGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brands extends Model
{
    use HasFactory;
    protected $fillable = [
       'title',
       'public_id',
       'fk_region_id',
       'image_profile_url',
       'image_cover_url'
    ];
    protected  $casts = [
        'id' => 'string',
        'image_profile_url' => ImageUrlGenerate::class,
        'image_cover_url' => ImageUrlGenerate::class,
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];
    protected $hidden = [
        'status'
    ];

    public function users()
    {
        return $this->hasMany(Users::class, 'fk_brand_id', 'id');
    }
    public function region()
    {
        return $this->hasOne(Regions::class, 'fk_region_id', 'id');
    }
   
}
