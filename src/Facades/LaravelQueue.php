<?php
/*
 * Copyright © 2022. Mohammad Al-Safadi(https://github.com/m-alsafadi) All rights reserved.
 */
namespace MAlsafadi\LaravelQueue\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \MAlsafadi\LaravelQueue\LaravelQueue
 *
 * Class LaravelQueue
 */
class LaravelQueue extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-queue';
    }
}
