<?php

namespace App\Services;

use App\Exceptions\InvalidRequestException;
use App\Models\ProfitMargin;
use App\Models\ViewResult;
use Exception;

class Formula
{

    public function getProfitMargin(ProfitMargin $data)
    {
        $result = new ViewResult();
        try {
            $data->profit = ($data->costOfItem * $data->markup) / 100;
            $data->salePrice = $data->costOfItem + $data->profit;
            // $data->profit = $data->salePrice - $data->costOfItem;
            // $data->markup = ($data->profit / $data->costOfItem) * 100;

            $data->grossMargin = ($data->profit /  $data->salePrice) * 100;


            $result->details = $data->roundValues(2);
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}
