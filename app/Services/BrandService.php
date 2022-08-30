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
    public function register(Brands $brand, Users $user = null)
    {
        $result = new ViewResult();
        try {
            $profileImage = new Images($brand->image_profile_url, Utility::$IMAGE_AVATARS);
            $coverImage   = new Images($brand->image_cover_url, Utility::$IMAGE_AVATARS);

            $brand->public_id           = $this::getBrandPublicId($brand);
            $brand->image_profile_url   = $profileImage->getPath();
            $brand->image_cover_url     = $coverImage->getPath();
            if (!$brand->save()) {
                throw new FailToSave("Brand");
            }
            if ($user) {
                $user->password         = Hash::make($user->password);
                $user->fk_usertype_id   = Utility::settings()->fk_def_brandreg_usertype_id;
                if (!$brand->users()->saveMany([
                    $user
                ])) {
                    throw new FailToSave("User");
                }
            }
            $profileImage->save();
            $coverImage->save();
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
            $brands = new Brands();
            $brands = Utility::prepareRelationships($criteria, $brands);

            if (isset($criteria->details['title'])) {
                $brands = $brands->where('title', 'LIKE', "%{$criteria->details['title']}%");
            }
            if (isset($criteria->details['public_id'])) {
                $brands = $brands->where('public_id', 'LIKE', "%{$criteria->details['public_id']}%");
            }
            $result->details = $brands->paginate(Utility::$PAGINATION_COUNT);

            $result->success();
        } catch (RelationNotFoundException $e) {
            $result->error($e);
            $result->message = "'" . $e->relation . "' relation does not exists";
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function updateBrand(array $param, $id)
    {
        $result = new ViewResult();
        try {
            $profileImage   = new Images($param['image_profile_url'], Utility::$IMAGE_AVATARS);
            $coverImage     = new Images($param['image_cover_url'], Utility::$IMAGE_AVATARS);

            $brand = Brands::find($id);
            $param['image_profile_url'] = $profileImage->getPath($brand->getRawOriginal('image_profile_url'));
            $param['image_cover_url'] = $coverImage->getPath($brand->getRawOriginal('image_cover_url'));
            $brand->update($param);
            // $brand->title= $param['title'];
            // $brand->image_profile_url= $profileImage->getPath($brand->getRawOriginal('image_profile_url'));
            // $brand->image_cover_url= $coverImage->getPath($brand->getRawOriginal('image_cover_url'));
            error_log($brand->image_profile_url);
            error_log($brand->image_cover_url);

            $result->complete($brand->save());
            if ($result->success) {
                $profileImage->save();
                $coverImage->save();
                $result->details = Brands::find($id);
            }
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function getMedias(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $records = CsFile::where(
                'fk_brand_id',
                '=',
                Auth::user()->fk_brand_id
            );

            if(isset($criteria->optional['ratio'])) {
                $records = $records->where('ratio','=',$criteria->optional['ratio']);
            }

            $records = $records->orderBy('id','DESC')->paginate(
                Utility::getPaginate($criteria->pagination)
            );

            if($criteria->pagination) {
                $records->appends(['pagination' => $criteria->pagination]);

                if(isset($criteria->optional['ratio'])) {
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
