<?php

use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\BrandsApiController;
use App\Http\Controllers\CategoriesApiController;
use App\Http\Controllers\CategoryAttributesApiController;
use App\Http\Controllers\ConditionsApiController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PackTypeApiController;
use App\Http\Controllers\ProductsApiController;
use App\Http\Controllers\ProdVariantsApiController;
use App\Http\Controllers\RegionsApiController;
use App\Http\Controllers\RolesApiController;
use App\Http\Controllers\UsersApiController;
use App\Http\Controllers\VariantsApiController;
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

Route::get('/', function () {
    return response()->json('welcome');
});

Route::post('login', [AuthApiController::class, 'login']);
Route::middleware('auth:sanctum')->get('logout', [AuthApiController::class, 'logout']);
Route::middleware('auth:sanctum')->get('sessions/revoke', [AuthApiController::class, 'revokeSessions']);
Route::middleware('auth:sanctum')->get('sessions/user', [AuthApiController::class, 'getCurrentUserFromCookie']);
Route::middleware('auth:sanctum')->put('sessions/user/password', [AuthApiController::class, 'changePassword']);


/** Categories */
Route::middleware('auth:sanctum')->get('categories', [CategoriesApiController::class, 'index']);
Route::middleware('auth:sanctum')->get('category-leaves', [CategoriesApiController::class, 'searchCategories']);
Route::middleware('auth:sanctum')->post('categories', [CategoriesApiController::class, 'store']);
Route::middleware('auth:sanctum')->get('categories/{id}/subcategories', [CategoriesApiController::class, 'getSubCategories']);
Route::middleware('auth:sanctum')->put('categories/{id}', [CategoriesApiController::class, 'update'])->where('id', '[0-9]+|exists:categories,id');
Route::middleware('auth:sanctum')->post('category-leaves', [CategoriesApiController::class, 'createCategoryLeaves']);
Route::middleware('auth:sanctum')->get('categories-withdepth/{depth}', [CategoriesApiController::class, 'getCategoriesByDepth'])->where('depth', '[0-9]+');
/** CategoryAttributes */
Route::middleware('auth:sanctum')->put('categories/{id}/attributes', [CategoryAttributesApiController::class, 'store'])->where('id','[0-9]+|exists:categories,id');
Route::middleware('auth:sanctum')->get('categories/{id}/attributes/setup', [CategoryAttributesApiController::class, 'getSetup'])->where('id','[0-9]+|exists:categories,id');
//id = level category id
Route::middleware('auth:sanctum')->get('categories/{id}/attributes', [CategoryAttributesApiController::class, 'index'])->where('id','[0-9]+|exists:categories,id');

Route::middleware('auth:sanctum')->get('test', [CategoryAttributesApiController::class, 'test']);



/** Users */
Route::middleware('auth:sanctum')->get('users', [UsersApiController::class, 'index']);
Route::middleware('auth:sanctum')->post('users/{userid}/privileges', [UsersApiController::class, 'savePrivileges'])->where('userid', '[0-9]+');
Route::middleware('auth:sanctum')->put('users/{id}', [UsersApiController::class, 'update'])->where('id', '[0-9]+|exists:users,id');

/** Regions */
Route::middleware('auth:sanctum')->apiResource('regions', RegionsApiController::class);

/** Roles */
Route::middleware('auth:sanctum')->apiResource('roles', RolesApiController::class);

/** Brands */
Route::post('brands/register', [BrandsApiController::class, 'store']);
Route::middleware('auth:sanctum')->get('brands', [BrandsApiController::class, 'index']);
Route::middleware('auth:sanctum')->get('brands/{id}', [BrandsApiController::class, 'show'])->where('id', '[0-9]+|exists:brands,id');
Route::middleware('auth:sanctum')->put('brands/{id}', [BrandsApiController::class, 'update'])->where('id', '[0-9]+|exists:brands,id');

/** Conditions */
Route::get('conditions', [ConditionsApiController::class, 'index']);
/** PackTypes */
Route::get('packtypes', [PackTypeApiController::class, 'index']);
/** Variants */
Route::get('variant-options/headers', [VariantsApiController::class, 'getHeaders']);
Route::get('variant-options/headers/{id}/details', [VariantsApiController::class, 'getDetails']);

/** Products */
Route::middleware('auth:sanctum')->post('products', [ProductsApiController::class, 'store']);
Route::middleware('auth:sanctum')->get('products', [ProductsApiController::class, 'index']);
Route::middleware('auth:sanctum')->get('products/{id}', [ProductsApiController::class, 'show']);
Route::middleware('auth:sanctum')->put('products/{id}', [ProductsApiController::class, 'update']);
Route::middleware('auth:sanctum')->get('products/{id}/{vid}', [ProdVariantsApiController::class, 'getById']);
Route::middleware('auth:sanctum')->put('prod-variants', [ProdVariantsApiController::class, 'update']);

Route::middleware('auth:sanctum')->get('brands/{brandId}/inventory/products', [ProdVariantsApiController::class, 'index'])
->where('brandId', '[0-9]+|exists:brands,id');
Route::post('upload',[FileController::class,'upload']);
