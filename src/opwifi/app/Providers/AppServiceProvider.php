<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Log, Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $monolog = Log::getMonolog();
        foreach($monolog->getHandlers() as $handler) {
          $handler->setLevel(Config::get('app.log-level'));
        }
    }
}
