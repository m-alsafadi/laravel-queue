<?php

namespace App\Jobs;

use MAlsafadi\LaravelQueue\Facades\LaravelQueue;
use MAlsafadi\LaravelQueue\Jobs\AbstractLaravelQueueJob;

/**
 *
 */
class Job extends AbstractLaravelQueueJob
{
    /**
     * Execute the job.
     *
     * @return bool|mixed|void
     */
    public function handle()
    {
        $model = Model::findOrFail($this->id);
        LaravelQueue::checkDateTime($model->valid_at, false, "Not set or its not past yet");

        return true;
    }
}

