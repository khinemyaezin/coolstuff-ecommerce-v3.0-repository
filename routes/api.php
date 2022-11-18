<?php

use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\BrandsApiController;
use App\Http\Controllers\CategoriesApiController;
use App\Http\Controllers\CategoryAttributesApiController;
use App\Http\Controllers\ConditionsApiController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\InventoryApiController;
use App\Http\Controllers\LocationApiController;
use App\Http\Controllers\OptionsApiController;
use App\Http\Controllers\PackTypeApiController;
use App\Http\Controllers\ProductsApiController;
use App\Http\Controllers\ProdVariantsApiController;
use App\Http\Controllers\RegionsApiController;
use App\Http\Controllers\RolesApiController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UsersApiController;
use App\Http\Controllers\UserTypesApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('login', [AuthApiController::class, 'login']);
Route::middleware('auth:sanctum')->get('logout', [AuthApiController::class, 'logout']);
Route::middleware('auth:sanctum')->get('sessions/revoke', [AuthApiController::class, 'revokeSessions']);
Route::middleware('auth:sanctum')->get('sessions/user', [AuthApiController::class, 'getCurrentUserFromCookie']);
Route::middleware('auth:sanctum')->put('sessions/user/password', [AuthApiController::class, 'changePassword']);


/** Categories */
Route::middleware('auth:sanctum')->get('categories', [CategoriesApiController::class, 'index']);
//Route::middleware('auth:sanctum')->get('categories/{id}/subcategories', [CategoriesApiController::class, 'getSubCategories']);
Route::middleware('auth:sanctum')->get('category-leaves', [CategoriesApiController::class, 'searchCategories']);
Route::middleware('auth:sanctum')->post('categories', [CategoriesApiController::class, 'store']);
Route::middleware('auth:sanctum')->put('categories/{id}', [CategoriesApiController::class, 'update'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->delete('categories/{id}', [CategoriesApiController::class, 'destroy'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->post('category-leaves', [CategoriesApiController::class, 'createCategoryLeaves']);
Route::middleware('auth:sanctum')->get('categories-withdepth/{depth}', [CategoriesApiController::class, 'getCategoriesByDepth'])->where('depth', '[0-9]+');
/** CategoryAttributes */
Route::middleware('auth:sanctum')->put('categories/{id}/attributes', [CategoryAttributesApiController::class, 'store'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->get('categories/{id}/attributes/setup', [CategoryAttributesApiController::class, 'getSetup'])->where('id', '[0-9]+');
//id = level category id
Route::middleware('auth:sanctum')->get('categories/{id}/attributes', [CategoryAttributesApiController::class, 'index'])->where('id', '[0-9]+');
/** Users */
Route::middleware('auth:sanctum')->get('users', [UsersApiController::class, 'index']);
Route::middleware('auth:sanctum')->get('users/{id}', [UsersApiController::class, 'show'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->post('users/{userid}/privileges', [UsersApiController::class, 'savePrivileges'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->put('users/{id}', [UsersApiController::class, 'update'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->get('usertypes/users', [UserTypesApiController::class, 'getUsersByUserTypes']);


/** Regions */
Route::middleware('auth:sanctum')->apiResource('regions', RegionsApiController::class);

/** Roles */
Route::middleware('auth:sanctum')->apiResource('roles', RolesApiController::class);

/** Brands */
Route::post('brands/register', [BrandsApiController::class, 'store']);
Route::middleware('auth:sanctum')->get('brands', [BrandsApiController::class, 'index']);
Route::middleware('auth:sanctum')->get('brand/setting', [BrandsApiController::class, 'getSettings']);
Route::middleware('auth:sanctum')->put('brand/setting', [BrandsApiController::class, 'updateSettings']);
Route::middleware('auth:sanctum')->get('brands/{id}', [BrandsApiController::class, 'show'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->put('brands/{id}', [BrandsApiController::class, 'update'])->where('id', '[0-9]+');

/** Conditions */
Route::get('conditions', [ConditionsApiController::class, 'index']);
/** PackTypes */
Route::get('packtypes', [PackTypeApiController::class, 'index']);
/** Variants */
Route::get('options/headers', [OptionsApiController::class, 'getHeaders']);
Route::post('options/headers', [OptionsApiController::class, 'saveHeader']);
Route::get('options/headers/{id}', [OptionsApiController::class, 'getHeaderById'])->where('id', '[0-9]+');
Route::get('options/headers/{id}/details', [OptionsApiController::class, 'getDetails'])->where('id', '[0-9]+');
Route::post('options/headers/{id}/details', [OptionsApiController::class, 'saveDetails'])->where('id', '[0-9]+');

Route::get('options/headers/{id}/units', [OptionsApiController::class, 'getUnits'])->where('id', '[0-9]+');

Route::put('options/headers/{id}', [OptionsApiController::class, 'update'])->where('id', '[0-9]+');
Route::delete('options/headers/{id}', [OptionsApiController::class, 'destory'])->where('id', '[0-9]+');



/** Products */
Route::middleware('auth:sanctum')->post('products', [ProductsApiController::class, 'store']);
Route::middleware('auth:sanctum')->get('products', [ProductsApiController::class, 'index']);
Route::middleware('auth:sanctum')->get('products/{id}', [ProductsApiController::class, 'show'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->put('products/{id}', [ProductsApiController::class, 'update'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->delete('products/{id}', [ProductsApiController::class, 'destroy'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->get('products/{id}/{vid}', [ProdVariantsApiController::class, 'getById'])->where('id', '[0-9]+')->where('vid', '[0-9]+');
Route::middleware('auth:sanctum')->put('products/{id}/{vid}', [ProdVariantsApiController::class, 'update'])->where('id', '[0-9]+')->where('vid', '[0-9]+');
Route::middleware('auth:sanctum')->delete('products/{id}/{vid}', [ProdVariantsApiController::class, 'delete'])->where('id', '[0-9]+')->where('vid', '[0-9]+');




Route::middleware('auth:sanctum')->get('brands/{brandId}/inventory/products', [InventoryApiController::class, 'getSingleProducts'])
    ->where('brandId', '[0-9]+');
Route::middleware('auth:sanctum')->put('brands/{brandId}/inventory/variants', [InventoryApiController::class, 'updateVariants'])
    ->where('brandId', '[0-9]+');

Route::middleware('auth:sanctum')->post('files', [FileUploadController::class, 'store']);
Route::middleware('auth:sanctum')->get('files', [FileUploadController::class, 'getMedias']);

/**
 * Locations
 */
Route::middleware('auth:sanctum')->get('locations', [LocationApiController::class, 'getLocations']);
Route::middleware('auth:sanctum')->get('locations/{id}', [LocationApiController::class, 'getLocationById'])->where('id', '[0-9]+|default');
Route::middleware('auth:sanctum')->post('locations', [LocationApiController::class, 'saveLocation']);
Route::middleware('auth:sanctum')->put('locations/{id}', [LocationApiController::class, 'updateLocation'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->put('locations/default', [LocationApiController::class, 'updateDefaultLocation']);
Route::middleware('auth:sanctum')->get('locations/by-product', [LocationApiController::class, 'getLocationsByProduct']);
Route::middleware('auth:sanctum')->put('locations/{id}/products/{prodId}', [LocationApiController::class, 'updateLocationQuantity'])->where('id', '[0-9]+')->where('prodId', '[0-9]+');;


/**
 * Test
 */
Route::get('test', [TestController::class, 'get']);
Route::post('test', [TestController::class, 'post']);
Route::put('test', [TestController::class, 'put']);
Route::delete('test', [TestController::class, 'delete']);
