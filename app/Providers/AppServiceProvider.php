<?php

namespace App\Providers;

use App\Models\Ajuste;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Carbon::setLocale('es');
        CarbonImmutable::setLocale('es');
        setlocale(LC_TIME, 'es_EC.UTF-8', 'es_ES.UTF-8', 'es');

        Model::preventLazyLoading(! $this->app->isProduction());
        Paginator::defaultView('components.paginacion');

        View::composer('*', function ($view) {
            static $ajustes;
            $view->with('clinica', $ajustes ??= Ajuste::todos());
        });
    }
}
