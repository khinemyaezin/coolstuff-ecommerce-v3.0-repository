<?php

namespace App\Services;

use App\Exceptions\FailToSave;
use App\Models\Brands;
use App\Models\Criteria;
use App\Models\CsFile;
use App\Models\Images;
use App\Models\Regions;
use App\Models\Users;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BrandService
{
    public static function getBrandPublicId(Brands $brand)
    {
        $prefixTitle = substr($brand->title, 0, 3);
        $countryCode = Regions::find($brand->fk_region_id)->country_code;
        $serial      = Brands::find(DB::table('brands')->max('id'))?->id + 1;
        $subfixSerial = sprintf("%'.05d", $serial ?? 1);
        return  strtoupper("{$prefixTitle}{$countryCode}{$subfixSerial}");
    }
    public function register(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $brand = new Brands();
            $brand->title = $criteria->details['brand']['title'];
            $brand->fk_region_id = $criteria->details['brand']['region_id'];
            $brand->profile_image = $criteria->details['brand']['profile_image'];
            $brand->cover_image = $criteria->details['brand']['cover_image'];
            $brand->public_id = $this::getBrandPublicId($brand);

            $user = new Users();
            $user->first_name = $criteria->details['user']['first_name'];
            $user->last_name = $criteria->details['user']['last_name'];
            $user->email = $criteria->details['user']['email'];
            $user->phone = $criteria->details['user']['phone'];
            $user->address = $criteria->details['user']['address'];
            $user->password = $criteria->details['user']['password'];

            $brand->save();
            if ($user) {
                $user->password = Hash::make($user->password);
                $user->fk_usertype_id = Common::settings()->fk_def_brandreg_usertype_id;

                $brand->users()->saveMany([
                    $user
                ]);
            }

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function getBrands(Criteria $criteria)
    {
        $result = new ViewResult();

        try {
            $brands = Common::prepareRelationships($criteria, new Brands());

            if (isset($criteria->httpParams['title'])) {
                $brands = $brands->where('title', 'LIKE', "%{$criteria->httpParams['title']}%");
            }
            if (isset($criteria->httpParams['public_id'])) {
                $brands = $brands->where('public_id', 'LIKE', "%{$criteria->httpParams['public_id']}%");
            }
            $result->details = $brands->paginate(config('constants.PAGINATION_COUNT'));

            $result->success();
        } catch (RelationNotFoundException $e) {
            $result->error($e);
            $result->message = "'" . $e->relation . "' relation does not exists";
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function updateBrand(Criteria $criteria, $id)
    {
        $result = new ViewResult();
        try {
            $brand = Brands::find($id);
            $brand->title = $criteria->details['title'];
            $brand->profile_image = $criteria->details['profile_image'];
            $brand->cover_image = $criteria->details['cover_image'];
            $brand->save();
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function getMedias(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $brand = Brands::find(Auth::user()->fk_brand_id);
            $records = $brand->files();

            if (isset($criteria->optional['ratio'])) {
                $records = $records->where('ratio', '=', $criteria->optional['ratio']);
            }

            $records = $records->orderBy('id', 'DESC')->paginate(
                Common::getPaginate($criteria->pagination)
            );

            if ($criteria->pagination) {
                $records->appends(['pagination' => $criteria->pagination]);

                if (isset($criteria->optional['ratio'])) {
                    $records->appends(['ratio' => $criteria->optional['ratio']]);
                }
            }
            $result->details = $records;
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}
