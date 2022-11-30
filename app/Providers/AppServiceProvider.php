<?php

namespace App\Providers;

use App\Daos\ProductsDao;
use App\Models\PersonalAccessToken;
use App\Models\SystemSettings;
use App\Services\AuthService;
use App\Services\BrandService;
use App\Services\CategoryAttributeService;
use App\Services\CategoryService;
use App\Services\ConditionsService;
use App\Services\LocationService;
use App\Services\PackTypeService;
use App\Services\ProductService;
use App\Services\RegionService;
use App\Services\RoleBasedAccessControl;
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
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService();
        });
        $this->app->singleton(RoleBasedAccessControl::class, function ($app) {
            return new RoleBasedAccessControl();
        });
        $this->app->singleton(RegionService::class, function ($app) {
            return new RegionService();
        });
        $this->app->singleton(TaskService::class, function ($app) {
            return new TaskService();
        });
        $this->app->singleton(BrandService::class, function ($app) {
            return new BrandService();
        });
        $this->app->singleton(CategoryService::class, function ($app) {
            return new CategoryService();
        });
        $this->app->singleton(ConditionsService::class, function ($app) {
            return new ConditionsService();
        });
        $this->app->singleton(PackTypeService::class, function ($app) {
            return new PackTypeService();
        });
        $this->app->singleton(VariantService::class, function ($app) {
            return new VariantService();
        });
        $this->app->singleton(CategoryAttributeService::class, function ($app) {
            return new CategoryAttributeService();
        });
     
        $this->app->singleton(ProductsDao::class, function ($app) {
            return new ProductsDao();
        });
        $this->app->singleton(LocationService::class, function ($app) {
            return new LocationService();
        });
        $this->app->singleton(ProductService::class, function ($app) {
            return new ProductService();
        });
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
