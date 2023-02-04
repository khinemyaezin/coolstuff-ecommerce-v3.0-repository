<?php

namespace App\Services\Impl;

use App\Models\Criteria;
use App\Models\Location;
use App\Models\ProdLocations;
use App\Models\ProdVariants;
use App\Models\ViewResult;
use App\Services\Common;
use App\Services\LocationService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LocationServiceImpl implements LocationService
{
    public function get(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $locations = new Location();
            $locations = Common::prepareRelationships($criteria, $locations);
            $brand = Auth::user()->brand;
            //dd($brand);
            if (!$brand) {
                throw new ModelNotFoundException();
            }

            $result->details = $locations->where('fk_brand_id', $brand->id)->orderBy('default', 'desc')->get();

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getById(Criteria $criteria, $id)
    {
        $result = new ViewResult();
        try {
            $locations = new Location();
            $locations = Common::prepareRelationships($criteria, $locations);

            if ($id == 'default') {
                $brand = Auth::user()->brand;
                if (!$brand) {
                    throw new ModelNotFoundException();
                }
                $result->details = $locations->where('fk_brand_id', $brand->id)->where('default', true)->first();
            } else {
                $result->details = $locations->findOrFail($id);
            }
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    /** @return ViewResult */
    public function save(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $isDefault = false;
            /**
             * Get brand from session
             */
            $brand = Auth::user()->brand;
            if (!$brand) {
                throw  new ModelNotFoundException();
            }
            /** Check default location */
            $locations = Location::where('fk_brand_id', $brand->id)->get();
            if (count($locations) == 0) {
                $isDefault = true;
            }
            $location = Location::create([
                "title" => $criteria->details['title'],
                "fk_brand_id" => $brand->id,
                "fk_region_id" => $criteria->details['region_id'],
                "default" => $isDefault
            ]);
            $result->details = $location;
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    /** @return ViewResult */
    public function update(Criteria $criteria, $id)
    {
        $result = new ViewResult();
        try {
            $location = Location::findOrFail($id);
            $location->title = $criteria->details['title'];
            $location->fk_region_id = $criteria->details['region_id'];
            $location->address = $criteria->details['address'];
            $location->apartment = $criteria->details['apartment'];
            $location->phone = $criteria->details['phone'];
            $location->save();
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    /** @return ViewResult */
    public function delete($id)
    {
        $result = new ViewResult();
        try {
            Location::findOrFail($id)->delete();
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function updateDefaultLocation(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $brand = ((object)Auth::user())->brand;
            $prevDefaultLocation = $brand->locations()->where('default', true)->first();
            if ($prevDefaultLocation) {
                $prevDefaultLocation->default = false;
                $prevDefaultLocation->save();
            }

            $nextDefaultLocation = Location::findOrFail($criteria->details['default']);
            $nextDefaultLocation->default = true;
            $nextDefaultLocation->save();

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getLocationByProduct($variantId)
    {
        $result = new ViewResult();
        try {
            $brand = Auth::user()->brand;
            if (!$brand) {
                throw  new ModelNotFoundException();
            }
            $warehouses = DB::table('locations as loc')
                ->leftJoin('prod_locations as prodloc', function ($join) use ($variantId) {
                    $join->whereRaw('loc.id = prodloc.fk_location_id');
                    $join->whereRaw('prodloc.fk_prod_variant_id = ? ', [$variantId]);
                })
                ->leftJoin('prod_variants as variant', function ($join) use ($variantId) {
                    $join->whereRaw('variant.id = prodloc.fk_prod_variant_id');
                    $join->whereRaw('variant.id = ? ', [$variantId]);
                })
                ->where('loc.fk_brand_id', '=', $brand->id)
                ->orderBy('loc.default', 'desc');

            $warehouses = $warehouses->select(
                [
                    "prodloc.id as id",
                    "loc.id as location.id",
                    "loc.title as location.title",
                    "loc.default as location.default",
                    "loc.address as location.address",
                    "variant.id as variant.id",
                    "prodloc.quantity as quantity"
                ]
            )->get();
            $result->details =  $warehouses->transform(function ($data) {
                return collect($data)->undot()->toArray();
            });
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function updateLocationQuantity(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $prodLocation = ProdLocations::where('fk_location_id', $criteria->request->route('id'))
                ->where('fk_prod_variant_id', $criteria->request->route('prodId'))->first();
            if (!$prodLocation) {
                $mnfe =  new ModelNotFoundException();
                $mnfe->setModel(Location::class, [$criteria->request->route('id')]);
                throw $mnfe;
            }
            $prodLocation->quantity = $criteria->details['new_quantity'];
            $prodLocation->save();
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function updateVariantDefLocationQty($variantId, $quantity)
    {
        $result = new ViewResult();
        try {
            $prodLocation = DB::table('prod_locations')->join('locations', function ($join) {
                $join->on('locations.id', '=', 'prod_locations.fk_location_id');
                $join->where('locations.default', '=', true);
            })
                ->where('prod_locations.fk_prod_variant_id', '=', $variantId)
                ->select(['prod_locations.id'])->first();

            if (!$prodLocation) throw new ModelNotFoundException();

            ProdLocations::findOrFail($prodLocation->id)->update([
                'quantity' => $quantity
            ]);
            $result->success();
        } catch (ModelNotFoundException $e) {
            $prodVariant = ProdVariants::findOrFail($variantId);
            $brand = Auth::user()->brand;
            if (!$brand) {
                throw new ModelNotFoundException();
            }
            $locationList = Location::where('fk_brand_id', $brand->id)
            ->orderBy('default', 'desc')->get();
            $locations = [];
            foreach ($locationList as $loc) {
                $locations[$loc->id] = [
                    'quantity' => $loc->default ? $quantity : 0,
                    'fk_prod_variant_id' => $prodVariant->id,
                ];
            }
            $prodVariant->locations()->sync($locations);
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}
