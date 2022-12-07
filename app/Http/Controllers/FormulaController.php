<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetProfitMarginRequest;
use App\Models\ProfitMargin;
use App\Services\Common;
use App\Services\Formula;

class FormulaController extends Controller
{
    function __construct(protected Formula $formulaService)
    {
    }

    public function getProfitMargin(GetProfitMarginRequest $request)
    {
        $request = $request->validated();
        $criteria = new ProfitMargin(
            Common::arrayVal($request,'sale_price'),
            (float) $request['cost_of_item'],
            Common::arrayVal($request,'markup')
        );  
        $result = $this->formulaService->getProfitMargin($criteria);
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
}
