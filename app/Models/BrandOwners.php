<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrandOwners extends Users
{
    use HasFactory;

    protected $fillable = [
        'id',
        'fk_brand_id'
    ];


    public function brand()
    {
        return $this->hasOne(Brands::class, 'id', 'fk_brand_id');
    }

    public function details()
    {
        return $this->morphOne(Users::class, 'userable');
    }
}
