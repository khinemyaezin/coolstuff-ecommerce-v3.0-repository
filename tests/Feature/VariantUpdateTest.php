<?php

namespace Tests\Feature;

use App\Enums\BizStatus;
use App\Models\ProdVariants;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VariantUpdateTest extends TestCase
{
    
    /**@test */
    public function test_example()
    {
         $result = $this->variantInventoryStatus(ProdVariants::find(11));
        // $this->assertTrue( );
    }
    public function variantInventoryStatus(ProdVariants $variant)
    {
        $result = [];
        $today = Carbon::now();
        $startAt = Carbon::instance($variant->start_at);
        $expiredAt = Carbon::instance($variant->expired_at);

        if ($variant->biz_status !== BizStatus::ACTIVE) {
            $result['message'] = BizStatus::getLabel(BizStatus::getByValue($variant->biz_status));
            return $result;
        }

        if ($today->lessThan($startAt)) {
            $result['message'] = "waiting";
            return $result;
        }
        if ($today->greaterThan($expiredAt)) {
            $result['message'] = "expired";
            return $result;
        }
        if ($variant->qty == 0) {
            $result['message'] = "outofstock";
            return $result;
        }

        return $result;
    }
}
