<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //在测试环境下可以通过在请求参数里添加sql_debug参数来开启SQL记录
        if (request()->input('sql_debug', false) && appEnv('APP_DEBUG', false)) {
            \DB::enableQueryLog();
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
