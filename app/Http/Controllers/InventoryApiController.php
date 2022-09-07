<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdVariantsUpdateRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryApiController extends Controller
{
    public function __construct(protected ProductService $service)
    {
        # code...
    }
    public function updateVariants(ProdVariantsUpdateRequest $request)
    {
    
        DB::beginTransaction();
        $result = $this->service->updateVariants($request->validated()['variants']);
        $result->completeTransaction();
        return response()->json($result);
    }
}
