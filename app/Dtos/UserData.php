<?php

namespace App\Dtos;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public $id;
    public $first_name;
    public $last_name;
    #[MapInputName('fk_usertype_id')]
    public $user_type;
    public $image_url;
    public $email;
    public $phone;
    public $address;
    public $profile_image;
}
