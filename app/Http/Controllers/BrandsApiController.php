<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Models\Brands;
use App\Models\Criteria;
use App\Models\Users;
use App\Models\ViewResult;
use App\Services\BrandService;
use App\Services\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrandsApiController extends Controller
{
    function __construct(protected BrandService $brandService)
    {
    }

    public function index(Request $request)
    {
        $result = null;
        $validator = validator($request->all(), [
            'relationships' => 'array',
            'details.*' => [
                'public_id' => 'string|nullable',
                'title' => 'string|nullable',
            ]
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $criteria = new Criteria();
            $criteria->relationships = $request['relationships'];
            $criteria->details = $request['details'];
            $result = $this->brandService->getBrands($criteria);
        }
        return response()->json($result);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        $result = null;
        $validator = validator($request->all(), [
            'brand.title' => 'string|max:200|min:2',
            'brand.region_id' => 'string|required|exists:regions,id',
            //'brand.image_profile_url' => array('regex:/(^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{2}==)?$)/u'),
            'user.first_name' => 'string|required',
            'user.last_name' => 'string|required',
            'user.email' => 'string|required|email|unique:users,email',
            'user.phone' => array('string', 'regex:/(^[0-9]+$)/u', 'nullable'),
            'user.address' => 'string|nullable',
            'user.password' => 'string|required',

        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $brand = new Brands();
            $brand->title = $request['brand.title'];
            $brand->fk_region_id = $request['brand.region_id'];
            $brand->image_profile_url = $request['brand.image_profile_url'];
            $brand->image_cover_url = $request['brand.image_cover_url'];

            $user = new Users();
            $user->first_name = $request['user.first_name'];
            $user->last_name = $request['user.last_name'];
            $user->email = $request['user.email'];
            $user->phone = $request['user.phone'];
            $user->address = $request['user.address'];
            $user->password = $request['user.password'];

            $result = $this->brandService->register($brand, $user);
        }
        $result->completeTransaction();
        return response()->json($result);
    }

    public function show($id)
    {
    }

    public function update($id)
    {
        DB::beginTransaction();
        $result = null;
        $request = request();
        $validator = validator($request->all(), [
            'title' => 'string|required|max:100',
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $param = [
                'title' => $request['title'],
                'image_profile_url' => $request['image_profile_url'],
                'image_cover_url' => $request['image_cover_url'],
            ];
            $result = $this->brandService->updateBrand($param, $id);
        }
        $result->completeTransaction();
        return response()->json($result);
    }

    public function destroy(Brands $brands)
    {
        //
    }
    
}
