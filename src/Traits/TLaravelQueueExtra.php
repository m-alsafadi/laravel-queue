<?php

namespace MAlsafadi\LaravelQueue\Traits;

use Illuminate\Support\Facades\Cache;
use MAlsafadi\LaravelQueue\LaravelQueueAbstract;

/**
 * @mixin \MAlsafadi\LaravelQueue\LaravelQueue
 */
trait TLaravelQueueExtra
{
    /**
     * Check if the given name exists in current queue.
     *
     * @param string|\Closure $name
     * @param bool|null       $is_fail
     *
     * @return bool
     */
    public function has(string|\Closure $name, ?bool $is_fail = null): bool
    {
        $name = value($name) ?: null;
        $this->debug("has: [$name]", [ __METHOD__, func_get_args() ]);

        $queues = $is_fail ? $this->get(null, $is_fail) : $this->queues;
        if( is_null($name) || empty($queues) ) {
            return false;
        }

        return isset($queues[ $name ]);
    }
    /**
     * Delete current storage.
     *
     * @param bool|null $is_fail
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function prune(?bool $is_fail = null): static
    {
        $this->debug("delete", [ __METHOD__, func_get_args() ]);
        if( $this->use_cache ) {
            $this->flushCache($is_fail);
        } else {
            $this->disk()->delete($this->getFilename($is_fail));
        }

        return $is_fail ? $this : $this->load();
    }


    /**
     * Empty current queue.
     *
     * @return $this
     */
    public function unload()
    {
        $this->debug("unload", [ __METHOD__, func_get_args() ]);
        $this->queues = [];

        return $this;
    }
}
