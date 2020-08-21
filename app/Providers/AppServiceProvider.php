<?php

namespace App\Providers;

use App\Repositories\GitlabRepository;
use App\Repositories\KairosDB;
use App\Services\GitlabService;
use GuzzleHttp\Client;
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
        $this->app->bind('Gitlab', function () {
            return new GitlabService(
                new GitlabRepository(
                    new Client()
                ),
                new KairosDB(
                    new Client()
                )
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
