<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // View Composer para o menu de módulos global
        View::composer('layouts.app', function ($view) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $modules = $user ? $user->getAccessibleModules() : collect([]);
            
            // Process modules just for display (no pinning logic needed here for simple link list)
            $allModules = collect($modules)->map(function ($module) {
                $module['slug'] = Str::slug($module['name']);
                return $module;
            });

            // Group by first letter for the mega menu
            $sortedModules = $allModules->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            $groupedModules = $sortedModules->groupBy(function ($item, $key) {
                return strtoupper(substr($item['name'], 0, 1));
            });

            $view->with('globalGroupedModules', $groupedModules);
        });
    }
}
