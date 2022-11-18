<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestRequest;
use App\Enums\BizStatus;
use App\Services\LocationService;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function get()
    {
        return response()->json( resolve(LocationService::class)->updateVariantDefLocationQty(6));
    }
    public function post(Request $request)
    {
        # code...
    }
    public function put(Request $request)
    {
        # code...
    }
    public function delete(Request $request)
    {
        # code...
    }
}
