<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTypes extends CsModel
{
    use HasFactory;
    protected $keyType = "string";
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];
    protected $fillable = [
        "id",
    ];

    public function users($relations = [])
    {
        return $this->hasMany(Users::class,'fk_usertype_id','id')->with($relations);
    }
}
