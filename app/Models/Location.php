<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory,CsModel;
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
        "default" => "boolean"
    ];
    protected $hidden = [
        'status'
    ];
    protected $fillable = [
        'id',
        "biz_status",
        "title",
        "fk_brand_id",
        "default",
        "fk_region_id",
        'address',
        'apartment',
        'phone'
    ];

    public function region()
    {
        return $this->hasOne(Regions::class, 'id','fk_region_id');
    }
}
