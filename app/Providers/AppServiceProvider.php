<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

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

        Mail::extend('brevo', function (array $config = []) {
            return (new BrevoTransportFactory)->create(
                new Dsn(
                    'brevo+api',
                    'default',
                    config('services.brevo.key')
                )
            );
        });

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

            // Pinned modules for sidebar sync
            $pinnedModules = collect([]);
            if ($user) {
                $paroquiaId = $user->paroquia_id ?? null;
                $pinnedRecords = \App\Models\PinnedModule::where('user_id', $user->id)
                    ->when($paroquiaId, fn($q) => $q->where('paroquia_id', $paroquiaId))
                    ->orderBy('order', 'asc')
                    ->get()
                    ->keyBy('module_slug');

                $pinnedSlugs = $pinnedRecords->keys()->toArray();

                $pinnedModules = $allModules
                    ->filter(fn($m) => in_array($m['slug'], $pinnedSlugs))
                    ->map(function ($m) use ($pinnedRecords) {
                        $m['order'] = $pinnedRecords[$m['slug']]->order ?? 0;
                        return $m;
                    })
                    ->sortBy('order')
                    ->values();
            }

            $view->with('globalGroupedModules', $groupedModules);
            $view->with('globalPinnedModules', $pinnedModules);
        });
    }
}
