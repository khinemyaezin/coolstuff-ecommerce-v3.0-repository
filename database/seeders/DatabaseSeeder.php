<?php

namespace Database\Seeders;

use App\Models\Brands;
use App\Models\Categories;
use App\Models\Conditions;
use App\Models\PackTypes;
use App\Models\Regions;
use App\Models\Roles;
use App\Models\RolesPrivileges;
use App\Models\SystemSettings;
use App\Models\Tasks;
use App\Models\UserPrivileges;
use App\Models\Users;
use App\Models\UserTypes;
use App\Models\VariantOptionDtls;
use App\Models\VariantOptionHdrs;
use App\Models\VariantOptionUnits;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        // Regions Imports
        try {
            Regions::truncate();
            $json = File::get("database/data/countries.json");
            $regions = json_decode($json);

            foreach ($regions as $key => $value) {
                Regions::create([
                    "country_name" => $value->countryName,
                    "country_code" => $value->countryCode,
                    "currency_code" => $value->currencyCode,
                ]);
            }

            // Roles Imports
            Roles::truncate();
            $json = File::get("database/data/roles.json");
            $roles = json_decode($json);

            foreach ($roles as $key => $value) {
                Roles::create([
                    "code" => $value->code,
                    "title" => $value->title,
                    "description" => $value->description,
                ]);
            }

            // Tasks Imports
            Tasks::truncate();
            $json = File::get("database/data/tasks.json");
            $task = json_decode($json);

            foreach ($task as $key => $value) {
                Tasks::create([
                    "title" => $value->title,
                ]);
            }

            // Usertypes Imports.
            UserTypes::truncate();
            $json = File::get("database/data/usertypes.json");
            $userType = json_decode($json);

            foreach ($userType as $key => $value) {
                UserTypes::create([
                    "title" => $value->title,
                ]);
            }

            // RolesPrivileges Imports
            RolesPrivileges::truncate();
            $json = File::get("database/data/role_task.json");
            $roleTask = json_decode($json);

            foreach ($roleTask as $key => $value) {
                RolesPrivileges::create([
                    "fk_role_id" => $value->fk_role_id,
                    "fk_task_id" => $value->fk_task_id,
                ]);
            }


            /** User roles */
            UserPrivileges::truncate();
            $json = File::get("database/data/user_role.json");
            $userPrivileges = json_decode($json);

            foreach ($userPrivileges as $key => $value) {
                // UserPrivileges::create([
                //     "title" => $value->title,
                //     "fk_user_id" => $value->fk_user_id,
                //     "fk_role_id" => $value->fk_role_id,
                // ]);
            }

            // Admin User Imports
            Users::truncate();
            $json = File::get("database/data/users.json");
            $data = json_decode($json);

            foreach ($data as $key => $value) {
                Users::create([
                    "first_name" => $value->first_name,
                    "last_name" => $value->last_name,
                    "fk_usertype_id" => $value->fk_usertype_id,
                    "email" => $value->email,
                    "password" => Hash::make($value->password)
                ]);
            }

            // Categories Import
            Categories::truncate();
            $json = File::get("database/data/categories.json");
            $data = json_decode($json);
            foreach ($data as $key => $value) {
                Categories::create([
                    "title" => $value->title,
                    "lft" => $value->lft,
                    "rgt" => $value->rgt,
                ]);
            }
            $sql = "with fullNode as (
                    SELECT node.id,node.title,array_to_string( array_remove(array_agg (parent.title ORDER BY parent.lft),'root'), ', ' ) as path,
                        (COUNT(parent.title) - 1) depth,node.lft,node.rgt
                        FROM categories AS node, categories AS parent
                        WHERE node.lft BETWEEN parent.lft AND parent.rgt 
                        GROUP BY node.id,node.title,node.lft
                        ORDER BY node.lft
                ) update categories set full_path = fullNode.path from fullNode where fullNode.id=categories.id;";
            DB::unprepared($sql);



            // Conditions Import
            Conditions::truncate();
            $json = File::get("database/data/conditions.json");
            $conditions = json_decode($json);

            foreach ($conditions as $key => $value) {
                Conditions::create([
                    "title" => $value->title,
                ]);
            }

            // Packtypes Import
            PackTypes::truncate();
            $json = File::get("database/data/packtypes.json");
            $packtypes = json_decode($json);

            foreach ($packtypes as $key => $value) {
                PackTypes::create([
                    "title" => $value->title,
                ]);
            }

            // System Settings
            SystemSettings::truncate();
            $json = File::get("database/data/system_settings.json");
            $settings = json_decode($json);

            SystemSettings::create([
                "fk_def_brandreg_usertype_id" => $settings->fk_def_brandreg_usertype_id,
            ]);

            //Variant Options
            VariantOptionHdrs::truncate();
            $json = File::get("database/data/variant_option_headers.json");
            $optionsheaders = json_decode($json);

            foreach ($optionsheaders as $key => $header) {
                $variantOptionHeader = VariantOptionHdrs::create([
                    "title" => $header->title,
                    "allow_dtls_custom_name" => $header->allow_dtls_custom_name,
                    "need_dtls_mapping" => $header->need_dtls_mapping,
                ]);
                foreach ($header->children as $key => $variantOptionDetail) {
                    VariantOptionDtls::create([
                        "fk_varopt_hdr_id" => $variantOptionHeader->id,
                        "title" => $variantOptionDetail->title,
                        "code" => $variantOptionDetail->code,
                    ]);
                }
                foreach ($header->units as $key => $unit) {
                    VariantOptionUnits::create([
                        "fk_varopt_hdr_id" => $variantOptionHeader->id,
                        "title" => $unit->title,
                        "code" => $unit->code,
                    ]);
                }
            }

            //Brand Imports.
            Brands::truncate();
            $json = File::get("database/data/brands.json");
            $brands = json_decode($json);

            foreach ($brands as $key => $brand) {
                $mybrand = Brands::create([
                    "title" => $brand->title,
                    "public_id" => $brand->public_id,
                    "fk_region_id" => $brand->fk_region_id,
                ]);
                foreach ($brand->users as $key => $user) {
                    $myuser = new Users([
                        "first_name" => $user->first_name,
                        "last_name" => $user->last_name,
                        "fk_usertype_id" => "2",
                        "email" => $user->email,
                        "password" => Hash::make($user->password)
                    ]);
                    $mybrand->users()->save(
                        $myuser
                    );
                }
            }

            $json = File::get('database/data/users.json');
            $users = json_decode($json);
            foreach ($users as $key => $user) {
                $myuser = new Users([
                    "first_name" => $user->first_name,
                    "last_name" => $user->last_name,
                    "fk_usertype_id" => "1",
                    "email" => $user->email,
                    "password" => Hash::make($user->password)
                ]);
                $myuser->save();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
}
