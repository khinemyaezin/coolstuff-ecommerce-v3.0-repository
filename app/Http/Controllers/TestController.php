<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestRequest;
use App\Enums\BizStatus;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function get(TestRequest $request)
    {
        return response()->json(BizStatus::ACTIVE->value);
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
