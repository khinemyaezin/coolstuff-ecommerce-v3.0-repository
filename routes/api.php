<?php

use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\BrandsApiController;
use App\Http\Controllers\CategoriesApiController;
use App\Http\Controllers\CategoryAttributesApiController;
use App\Http\Controllers\ConditionsApiController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\FormulaController;
use App\Http\Controllers\InventoryApiController;
use App\Http\Controllers\LocationApiController;
use App\Http\Controllers\OptionsApiController;
use App\Http\Controllers\PackTypeApiController;
use App\Http\Controllers\ProductsApiController;
use App\Http\Controllers\ProdVariantsApiController;
use App\Http\Controllers\RegionsApiController;
use App\Http\Controllers\RoleBasedAccessController;
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
Route::middleware(['auth:sanctum'])->get('logout', [AuthApiController::class, 'logout']);


/** 
 * Categories
 */
Route::middleware(['auth:sanctum', 'abilities:categories_read'])->get('categories', [CategoriesApiController::class, 'index']);
Route::middleware(['auth:sanctum', 'abilities:category_create'])->post('categories', [CategoriesApiController::class, 'store']);
Route::middleware(['auth:sanctum', 'abilities:category_update'])->put('categories/{id}', [CategoriesApiController::class, 'update'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum', 'abilities:category_delete'])->delete('categories/{id}', [CategoriesApiController::class, 'destroy'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum', 'abilities:categories_read'])->get('category-leaves', [CategoriesApiController::class, 'searchCategories']);
Route::middleware(['auth:sanctum', 'abilities:category_create'])->post('category-leaves', [CategoriesApiController::class, 'createCategoryLeaves']);
Route::middleware(['auth:sanctum', 'abilities:categories_read'])->get('categories-withdepth/{depth}', [CategoriesApiController::class, 'getCategoriesByDepth'])->where('depth', '[0-9]+');

/** 
 * CategoryAttributes
 */
Route::middleware(['auth:sanctum', 'abilities:category_attributes_update'])->put('categories/{id}/attributes', [CategoryAttributesApiController::class, 'store'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum', 'abilities:category_attributes_read'])->get('categories/{id}/attributes', [CategoryAttributesApiController::class, 'index'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum', 'abilities:category_attribute_read'])->get('categories/{id}/attributes/setup', [CategoryAttributesApiController::class, 'getSetup'])->where('id', '[0-9]+');

/** 
 * Users
 */
Route::middleware(['auth:sanctum', 'abilities:users_read'])->get('users', [UsersApiController::class, 'index']);
Route::middleware(['auth:sanctum'])->post('users', [UsersApiController::class, 'store']);
Route::middleware(['auth:sanctum', 'abilities:user_read'])->get('users/{id}', [UsersApiController::class, 'show'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum', 'abilities:user_update'])->put('users/{id}', [UsersApiController::class, 'update'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum', 'abilities:user_pass_update'])->put('users/{id}/password', [AuthApiController::class, 'changePassword'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum'])->get('users/{id}/roles', [UsersApiController::class, 'getAvaliableRoles'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum'])->put('users/{id}/roles', [UsersApiController::class, 'saveRoleUser'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum', 'abilities:sessions_read'])->get('users/{id}/sessions', [AuthApiController::class, 'getUserSessions'])->where('id', '[0-9]+');

Route::middleware(['auth:sanctum', 'abilities:usertype_users_read'])->get('usertypes/users', [UserTypesApiController::class, 'getUsersByUserTypes']);

Route::middleware(['auth:sanctum'])->get('current-user/session', [AuthApiController::class, 'getCurrentUserFromCookie']);
Route::middleware(['auth:sanctum'])->delete('users/{userid}/sessions/{sessionid}', [AuthApiController::class, 'revokeUserSession']);
Route::middleware(['auth:sanctum', 'abilities:sessions_delete'])->delete('users/{id}/sessions', [AuthApiController::class, 'revokeSessions']);


/** 
 * Regions
 */
Route::middleware('guest')->apiResource('regions', RegionsApiController::class);

/** 
 * Roles
 */
Route::middleware('auth:sanctum')->get('access-control/roles', [RoleBasedAccessController::class, 'getRoles']);
Route::middleware('auth:sanctum')->post('access-control/roles', [RoleBasedAccessController::class, 'saveRole']);
Route::middleware('auth:sanctum')->put('access-control/roles/{id}', [RoleBasedAccessController::class, 'updateRole'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->get('access-control/roles/{id}/tasks', [RoleBasedAccessController::class, 'getTaskByRoleID'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->get('access-control/tasks', [RoleBasedAccessController::class, 'getTasks']);


/** 
 * Brands
 */
Route::post('brands/register', [BrandsApiController::class, 'store']);
Route::middleware('auth:sanctum')->get('brands', [BrandsApiController::class, 'index']);
Route::middleware('auth:sanctum')->get('brand/setting', [BrandsApiController::class, 'getSettings']);
Route::middleware('auth:sanctum')->put('brand/setting', [BrandsApiController::class, 'updateSettings']);
Route::middleware('auth:sanctum')->get('brands/{id}', [BrandsApiController::class, 'show'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->put('brands/{id}', [BrandsApiController::class, 'update'])->where('id', '[0-9]+');
Route::middleware('auth:sanctum')->put('brands/{id}/description', [BrandsApiController::class, 'updateBio'])->where('id', '[0-9]+');

/** 
 * Conditions
 */
Route::get('conditions', [ConditionsApiController::class, 'index']);

/** 
 * PackTypes
 */
Route::get('packtypes', [PackTypeApiController::class, 'index']);

/** 
 * Variants
 */
Route::get('options/headers', [OptionsApiController::class, 'getHeaders']);
Route::post('options/headers', [OptionsApiController::class, 'saveHeader']);
Route::get('options/headers/{id}', [OptionsApiController::class, 'getHeaderById'])->where('id', '[0-9]+');
Route::get('options/headers/{id}/details', [OptionsApiController::class, 'getDetails'])->where('id', '[0-9]+');
Route::post('options/headers/{id}/details', [OptionsApiController::class, 'saveDetails'])->where('id', '[0-9]+');
Route::get('options/headers/{id}/units', [OptionsApiController::class, 'getUnits'])->where('id', '[0-9]+');
Route::put('options/headers/{id}', [OptionsApiController::class, 'update'])->where('id', '[0-9]+');
Route::delete('options/headers/{id}', [OptionsApiController::class, 'destory'])->where('id', '[0-9]+');

/** 
 * Products
 */
Route::middleware(['auth:sanctum'])->post('products', [ProductsApiController::class, 'store']);
Route::middleware(['auth:sanctum', 'abilities:products_read'])->get('products', [ProductsApiController::class, 'index']);
Route::middleware(['auth:sanctum', 'abilities:product_read'])->get('products/{id}', [ProductsApiController::class, 'show'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum', 'abilities:product_update'])->put('products/{id}', [ProductsApiController::class, 'update'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum', 'abilities:product_delete'])->delete('products/{id}', [ProductsApiController::class, 'destroy'])->where('id', '[0-9]+');
Route::middleware(['auth:sanctum'])->get('products/{id}/{vid}', [ProdVariantsApiController::class, 'getById'])->where('id', '[0-9]+')->where('vid', '[0-9]+');
Route::middleware(['auth:sanctum'])->put('products/{id}/{vid}', [ProdVariantsApiController::class, 'update'])->where('id', '[0-9]+')->where('vid', '[0-9]+');
Route::middleware(['auth:sanctum'])->delete('products/{id}/{vid}', [ProdVariantsApiController::class, 'delete'])->where('id', '[0-9]+')->where('vid', '[0-9]+');


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
 * Formulas
 */
Route::middleware('auth:sanctum')->post('formulas/profit-margin', [FormulaController::class, 'getProfitMargin']);

