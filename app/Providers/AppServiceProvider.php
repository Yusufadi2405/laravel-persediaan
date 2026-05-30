<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Models\Admin\WebModel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
          // Share data web ke semua view
        view()->share('web', WebModel::first());
        if(env(key:'APP_ENV') !== 'local'){
            URL::forceScheme( scheme: 'https' );
        }
    }
}
