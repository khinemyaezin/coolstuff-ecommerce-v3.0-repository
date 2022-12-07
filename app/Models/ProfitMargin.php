<?php

namespace App\Models;

class ProfitMargin 
{
    public float $grossMargin;
    public float $profit;

    function __construct(
        public $salePrice,
        public float $costOfItem,
        public $markup,

    ) {
    }

    function roundValues(int $decimalPlace){
        foreach ($this as $key => $value) {
            $this->$key = round($value,$decimalPlace);
        }
        return $this;
    }
}
