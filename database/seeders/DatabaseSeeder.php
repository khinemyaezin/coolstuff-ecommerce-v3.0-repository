<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\Conditions;
use App\Models\PackTypes;
use App\Models\Regions;
use App\Models\Roles;
use App\Models\RolesPrivileges;
use App\Models\SystemSettings;
use App\Models\Tasks;
use App\Models\UserTypes;
use App\Models\VariantOptionDtls;
use App\Models\VariantOptionHdrs;
use App\Models\VariantOptionUnits;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Regions::truncate();
        $this->prepare(
            "database/data/countries.json",
            function ($array) {
                foreach ($array as $key => $value) {
                    Regions::create([
                        "country_name" => $value->countryName,
                        "country_code" => $value->countryCode,
                        "currency_code" => $value->currencyCode,
                    ]);
                }
            }
        );

        Roles::truncate();
        $this->prepare(
            "database/data/roles.json",
            function ($array) {
                foreach ($array as $key => $value) {
                    Roles::create([
                        "code" => $value->code,
                        "title" => $value->title,
                        "description" => $value->description,
                    ]);
                }
            }
        );


        Tasks::truncate();
        $this->prepare("database/data/tasks.json", function ($array) {
            foreach ($array as $key => $value) {
                Tasks::create([
                    "id" => $value->code,
                ]);
            }
        });

        UserTypes::truncate();
        $this->prepare("database/data/usertypes.json", function ($array) {
            foreach ($array as $key => $value) {
                UserTypes::create([
                    "id" => $value->title,
                ]);
            }
        });

     
        Categories::truncate();
        $this->prepare("database/data/categories.json", function ($array) {
            foreach ($array as $key => $value) {
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
        });

        Conditions::truncate();
        $this->prepare("database/data/conditions.json", function ($array) {
            foreach ($array as $key => $value) {
                Conditions::create([
                    "title" => $value->title,
                ]);
            }
        });

        // Packtypes Import
        PackTypes::truncate();
        $this->prepare("database/data/packtypes.json", function ($array) {
            foreach ($array as $key => $value) {
                PackTypes::create([
                    "title" => $value->title,
                ]);
            }
        });

        // System Settings
        SystemSettings::truncate();
        $this->prepare("database/data/system_settings.json", function ($array) {
            SystemSettings::create([
                "fk_def_brandreg_usertype_id" => $array->fk_def_brandreg_usertype_id,
            ]);
        });

        //Variant Options
        VariantOptionHdrs::truncate();
        $this->prepare("database/data/variant_option_headers.json", function ($array) {
            foreach ($array as $key => $header) {
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
        });

        
    
    }

    public function prepare($path, $callback)
    {
        $json = File::get($path);
        $array = json_decode($json);
        $callback($array);
    }
}
