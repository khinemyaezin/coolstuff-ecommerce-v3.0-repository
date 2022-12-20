<?php

namespace Database\Seeders;

use App\Models\Brands;
use App\Models\Roles;
use App\Models\UserPrivileges;
use App\Models\Users;
use App\Models\UserTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class RolesSeeder extends Seeder
{
    public function run()
    {
        
        $this->prepare("database/data/users.json", function ($array) {
            foreach ($array as $key => $value) {
                Users::create([
                    "first_name" => $value->first_name,
                    "last_name" => $value->last_name,
                    "fk_usertype_id" => $value->fk_usertype_id,
                    "email" => $value->email,
                    "password" => Hash::make($value->password)
                ]);
            }
        });

        // $this->prepare("database/data/brands.json", function ($array) {
        //     foreach ($array as $key => $brand) {
        //         $mybrand = Brands::create([
        //             "title" => $brand->title,
        //             "public_id" => $brand->public_id,
        //             "fk_region_id" => $brand->fk_region_id,
        //         ]);
        //         foreach ($brand->users as $key => $user) {
        //             $mybrand->users()->save(
        //                 new Users([
        //                     "first_name" => $user->first_name,
        //                     "last_name" => $user->last_name,
        //                     "fk_usertype_id" => "brand_owner",
        //                     "email" => $user->email,
        //                     "password" => Hash::make($user->password)
        //                 ])
        //             );
        //         }
        //     }
        // });
        
        //  UserPrivileges::truncate();
        // $this->prepare("database/data/user_role.json", function ($array) {

        //     foreach ($array as $key => $value) {
        //         UserPrivileges::create([
        //             "title" => $value->title,
        //             "fk_user_id" => $value->fk_user_id,
        //             "fk_role_id" => $value->fk_role_id,
        //         ]);
        //     }
        // });

    }
    public function prepare($path, $callback)
    {
        $json = File::get($path);
        $array = json_decode($json);
        $callback($array);
    }
}
