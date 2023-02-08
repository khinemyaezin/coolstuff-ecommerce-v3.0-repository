<?php

namespace App\Models;

use App\Casts\ImageUrlGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

class Users extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CsModel;

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
        'image_url',
        'email',
        'phone',
        'address',
        'password',
        'profile_image',
        'userable_type',
        'userable_id'
    ];


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

    public function profileImage()
    {
        return $this->hasOne(CsFile::class, 'id', 'profile_image');
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

    public function createToken(string $name, array $abilities = ['*'])
    {
        //dd(request()->header('User-Agent'));
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = \Illuminate\Support\Str::random(40)),
            'abilities' => $abilities,
            "user_agent" => request()->header('User-Agent'),
            "ip" => request()->ip(),
        ]);

        return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    public function userable()
    {
        return $this->morphTo();
    }

    
}
