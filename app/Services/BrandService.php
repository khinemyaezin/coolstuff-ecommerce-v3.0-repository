<?php
namespace App\Services;

use App\Models\Brands;
use App\Models\Criteria;

interface BrandService {
    public static function getBrandPublicId(Brands $brand);
    public function register(Criteria $criteria);
    public function getBrands(Criteria $criteria);
    public function getBrandByID(Criteria $criteria);
    public function updateBrand(Criteria $criteria, $id);
    public function getMedias(Criteria $criteria);
    public function getSettings();
    public function updateSetting(Criteria $criteria);
    public function updateBio(Criteria $criteria);
}