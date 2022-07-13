<?php

namespace MAlsafadi\LaravelQueue\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * @ mixin \MAlsafadi\LaravelQueue\LaravelQueue
 */
trait TLaravelQueueCache
{
// region: cache
    public function getCacheKey(?bool $is_fail = null): string
    {
        $this->debug("get cache key", [ __METHOD__, func_get_args() ]);

        return "laravel-queue" . ($is_fail === true ? "-fails" : ($is_fail === false ? "-success" : ""));
    }

    /**
     * @param string|\Closure|null $name
     *
     * @return mixed
     */
    public function getCache(?bool $is_fail = null)
    {
        $this->debug("get cache", [ __METHOD__, func_get_args() ]);
        if( !$this->hasCache($is_fail) ) {
            $this->putCache([], $is_fail);
        }

        return Cache::rememberForever($this->getCacheKey($is_fail), fn() => []);
    }

    /**
     * @param string|\Closure|null $name
     * @param                      $value
     *
     * @return \MAlsafadi\LaravelQueue\LaravelQueue
     */
    public function putCache($value = null, ?bool $is_fail = null)
    {
        $this->debug("put cache", [ __METHOD__, func_get_args() ]);
        $value = value($value) ?: [];

        Cache::forever($this->getCacheKey($is_fail), $value);

        return $this;
    }

    /**
     * @param string|\Closure|null $name
     *
     * @return bool
     */
    public function hasCache(?bool $is_fail = null)
    {
        $this->debug("has cache", [ __METHOD__, func_get_args() ]);

        return Cache::has($this->getCacheKey($is_fail));
    }

    /**
     * @return $this
     */
    public function flushCache(?bool $is_fail = null)
    {
        $this->debug("flush cache", [ __METHOD__, func_get_args() ]);
        Cache::forget($this->getCacheKey($is_fail));

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataCache()
    {
        $this->debug("get data cache", [ __METHOD__, func_get_args() ]);
        if( !$this->hasDataCache() ) {
            $this->putDataCache([]);
        }

        return Cache::rememberForever($this->getCacheKey() . ".data", fn() => []);
    }

    /**
     * @param                      $value
     *
     * @return $this
     */
    public function putDataCache($value = null)
    {
        $this->debug("put data cache", [ __METHOD__, func_get_args() ]);
        $value = value($value) ?: [];

        Cache::forever($this->getCacheKey() . ".data", $value);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDataCache()
    {
        $this->debug("has data cache", [ __METHOD__, func_get_args() ]);

        return Cache::has($this->getCacheKey() . ".data");
    }

    /**
     * @return $this
     */
    public function flushDataCache()
    {
        $this->debug("flush data cache", [ __METHOD__, func_get_args() ]);
        Cache::forget($this->getCacheKey() . ".data");

        return $this;
    }
// endregion: cache
}
