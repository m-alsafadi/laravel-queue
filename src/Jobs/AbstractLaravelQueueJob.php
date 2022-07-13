<?php

namespace MAlsafadi\LaravelQueue\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MAlsafadi\LaravelQueue\Facades\LaravelQueue;

/**
 *
 */
abstract class AbstractLaravelQueueJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int|string|null $id = 0;
    protected \DateTime|string|null $run_at = null;

    /**
     * Create a new job instance.
     *
     * @param int|string|null       $id
     * @param \DateTime|string|null $runt_at
     */
    public function __construct($id, $runt_at = null)
    {
        $this->id = $id;
        $this->run_at = $runt_at;
    }

    /**
     * Execute the job.
     *
     * @return bool|mixed|void
     */
    abstract public function handle();

    public function shouldRun(): bool
    {
        return is_null($this->run_at) || Carbon::parse($this->run_at)->isPast();
    }

    /**
     * @return mixed
     */
    public function callHandler()
    {
        return $this->shouldRun() ? call_user_func([ $this, 'handle' ]) : null;
    }

    /**
     * @param $arguments
     * @param $name
     * @param $queue
     *
     * @return static|null
     */
    public static function makeLaravelQueueInstance($arguments, $name, $queue)
    {
        if( !method_exists(static::class, 'handle') ) {
            return null;
        }

        $arguments = array_wrap(!$arguments ? [] : $arguments);
        $date = data_get($queue, 'date');
        $id = data_get($queue, 'model_id');
        $class = new static($id, $date, ...$arguments);

        return $class->shouldRun() ? $class : null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \DateTime|null                      $valid_at
     * @param array|null                          $arguments
     *
     * @return \MAlsafadi\LaravelQueue\Facades\LaravelQueue
     * @throws \Throwable
     */
    public static function addJob(\Illuminate\Database\Eloquent\Model $model, ?\DateTime $valid_at = null, ?array $arguments = null)
    {
        return LaravelQueue::addJob($model, static::class, $valid_at, $arguments);
    }
}

