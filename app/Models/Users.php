<?php

namespace App\Models;

use App\Casts\ImageUrlGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'id',
        'biz_status',
        'first_name',
        'last_name',
        'fk_nrc_state_id',
        'fk_nrc_district_id',
        'fk_nrc_nation_id',
        'nrc_value',
        'fk_usertype_id',
        'fk_brand_id',
        'image_url',
        'email',
        'phone',
        'address',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'fk_nrc_state_id',
        'fk_nrc_district_id',
        'fk_nrc_nation_id',
        'fk_nrc_state_id',
        'fk_nrc_district_id',
        'fk_nrc_nation_id',
        'fk_usertype_id',
        'fk_brand_id',
        'email_verified_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected  $casts = [
        'id' => 'string',
        'image_url' => ImageUrlGenerate::class,
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];

    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'user_privileges', 'fk_user_id', 'fk_role_id');
    }

    public function userType()
    {
        return $this->hasOne(UserTypes::class, 'id', 'fk_usertype_id');
    }

    public function brand()
    {
        return $this->hasOne(Brands::class, 'id', 'fk_brand_id');
    }

    public function nrcState()
    {
        return $this->hasOne(NrcStates::class, 'id', 'fk_nrc_state_id');
    }

    public function nrcDistrict()
    {
        return $this->hasOne(NrcDistricts::class, 'id', 'fk_nrc_district_id');
    }
    public function nrcNation()
    {
        return $this->hasOne(NrcNations::class, 'id', 'fk_nrc_nation_id');
    }
}
