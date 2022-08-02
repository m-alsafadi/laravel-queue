<?php

namespace MAlsafadi\LaravelQueue\Traits;

use MAlsafadi\LaravelQueue\Facades\LaravelQueue;

/**
 *
 */
trait TLaravelQueueModel
{
    /**
     * @param string|\MAlsafadi\LaravelQueue\Jobs\AbstractLaravelQueueJob $job
     * @param \DateTime|null                                              $valid_at
     * @param array|null                                                  $arguments
     * @param string|null                                                 $name
     *
     * @return $this
     * @throws \Throwable
     */
    public function addJob($job, ?\DateTime $valid_at = null, ?array $arguments = null, ?string $name = null)
    {
        LaravelQueue::addJob($this, $job, $valid_at, $arguments, $name);

        return $this;
    }
}
