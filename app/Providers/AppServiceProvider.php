<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Models\SystemSettings;
use App\Services\AuthService;
use App\Services\BrandService;
use App\Services\CategoryAttributesService;
use App\Services\CategoryService;
use App\Services\ConditionsService;
use App\Services\Formula;
use App\Services\Impl\AuthServiceImpl;
use App\Services\Impl\BrandServiceImpl;
use App\Services\Impl\CategoryAttributeServiceImpl;
use App\Services\Impl\CategoryServiceImpl;
use App\Services\Impl\ConditionsServiceImpl;
use App\Services\Impl\InventoryServiceImpl;
use App\Services\Impl\LocationServiceImpl;
use App\Services\Impl\PackTypeServiceImpl;
use App\Services\Impl\ProductServiceImpl;
use App\Services\Impl\ProductVariantServiceImpl;
use App\Services\Impl\RegionServiceImpl;
use App\Services\Impl\RoleBasedAccessControlImpl;
use App\Services\Impl\TaskServiceImpl;
use App\Services\Impl\UserServiceImpl;
use App\Services\Impl\VariantServiceImpl;
use App\Services\InventoryService;
use App\Services\LocationService;
use App\Services\PackTypeService;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use App\Services\RegionService;
use App\Services\RolebasedAccessControl;
use App\Services\TaskService;
use App\Services\UserService;
use App\Services\VariantService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UserService::class, UserServiceImpl::class);
        $this->app->singleton(RolebasedAccessControl::class, RoleBasedAccessControlImpl::class);
        $this->app->singleton(RegionService::class,RegionServiceImpl::class);
        $this->app->singleton(TaskService::class, TaskServiceImpl::class);
        $this->app->singleton(BrandService::class, BrandServiceImpl::class);
        $this->app->singleton(CategoryService::class,CategoryServiceImpl::class);
        $this->app->singleton(ConditionsService::class, ConditionsServiceImpl::class);
        $this->app->singleton(PackTypeService::class, PackTypeServiceImpl::class);
        $this->app->singleton(VariantService::class, VariantServiceImpl::class);
        $this->app->singleton(CategoryAttributesService::class,CategoryAttributeServiceImpl::class);
        $this->app->singleton(LocationService::class,LocationServiceImpl::class);
        $this->app->singleton(ProductService::class, ProductServiceImpl::class);
        $this->app->singleton(Formula::class);
        $this->app->singleton(InventoryService::class, InventoryServiceImpl::class);
        $this->app->singleton(ProductVariantService::class, ProductVariantServiceImpl::class);
        $this->app->singleton(AuthService::class, AuthServiceImpl::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Sanctum::ignoreMigrations();
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        config([
            'settings' => SystemSettings::first()
        ]);
        Paginator::useBootstrap();
    }
}
