<?php

namespace App\Providers;

use App\Models\SystemSettings;
use App\Services\BrandService;
use App\Services\CategoryAttributeService;
use App\Services\CategoryService;
use App\Services\ConditionsService;
use App\Services\PackTypeService;
use App\Services\ProductService;
use App\Services\RegionService;
use App\Services\RoleService;
use App\Services\TaskService;
use App\Services\UserService;
use App\Services\VariantService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

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
        $this->app->singleton(RoleService::class, function ($app) {
            return new RoleService();
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
        config([
            'settings' => SystemSettings::first()
        ]);
        Paginator::useBootstrap();
    }
}
