<?php
/*
 * Copyright © 2022. Mohammad Al-Safadi(https://github.com/m-alsafadi) All rights reserved.
 */

namespace MAlsafadi\LaravelQueue\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use MAlsafadi\LaravelQueue\Commands\LaravelQueueCommand;
use MAlsafadi\LaravelQueue\Commands\NewLaravelQueueJobCommand;
use MAlsafadi\LaravelQueue\Commands\StartLaravelQueueCommand;
use MAlsafadi\LaravelQueue\Facades\LaravelQueue;

/**
 * Class LaravelQueueProvider
 *
 * @package MAlsafadi\LaravelQueue\Providers
 */
class LaravelQueueProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'laravel-queue');

        $this->app->singleton('laravel-queue', function($app, $parameters) {
            return new \MAlsafadi\LaravelQueue\LaravelQueue($app);
        });

        AliasLoader::getInstance()->alias('LaravelQueue', \MAlsafadi\LaravelQueue\Facades\LaravelQueue::class);

        $this->commands([
                            StartLaravelQueueCommand::class,
                            NewLaravelQueueJobCommand::class,
                            LaravelQueueCommand::class,
                        ]);
    }

    /**
     * Bootstrap services.
     *
     * @param Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        if( $this->app->runningInConsole() ) {
            $this->publishes([
                                 $this->configPath() => config_path('laravel-queue.php'),
                                 $this->workerPath() => base_path('laravel-queue'),
                             ], 'laravel-queue');
        }

        $this->app->booted(function() {
            $this->routes();
        });

        $this->loadViewsFrom(__DIR__.'/../views', 'laravel-queue');

    }

    /**
     * Register the field's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if( $this->app->routesAreCached() ) {
            return;
        }

        Route::prefix('laravel-queue')
             ->as('laravel-queue')
             ->group(__DIR__ . '/../routes/web.php');
    }

    /**
     * Set the config path
     *
     * @return string
     */
    protected function configPath()
    {
        return __DIR__ . '/../../config/laravel-queue.php';
    }

    /**
     * Set the worker path
     *
     * @return string
     */
    protected function workerPath()
    {
        return __DIR__ . '/../../laravel-queue';
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [ LaravelQueue::class ];
    }
}
