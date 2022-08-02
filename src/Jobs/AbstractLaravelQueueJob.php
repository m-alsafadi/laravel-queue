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
    protected array|null $arguments = null;

    /**
     * Create a new job instance.
     *
     * @param int|string|null       $id
     * @param \DateTime|string|null $runt_at
     * @param array|null            $arguments
     */
    public function __construct($id, $runt_at = null, ?array $arguments = null)
    {
        $this->id = $id;
        $this->run_at = $runt_at;
        $this->arguments = $arguments;
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
        return $this->shouldRun() ? call_user_func_array([ $this, 'handle' ], (array) $this->arguments) : null;
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
        $class = new static($id, $date, $arguments);

        return $class->shouldRun() ? $class : null;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \DateTime|null                      $valid_at
     * @param array|null                          $arguments
     * @param string|null                         $name
     *
     * @return \MAlsafadi\LaravelQueue\Facades\LaravelQueue
     * @throws \Throwable
     */
    public static function addJob(\Illuminate\Database\Eloquent\Model $model, ?\DateTime $valid_at = null, ?array $arguments = null, ?string $name = null)
    {
        return LaravelQueue::addJob($model, static::class, $valid_at, $arguments, $name);
    }

    /**
     * Get job model & find id
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(): ?\Illuminate\Database\Eloquent\Model
    {
        $current = LaravelQueue::getCurrentQueue();
        $name = key($current);
        $queue = $current[ $name ];
        $model = data_get($queue, 'model');
        $model_id = data_get($queue, 'model_id');

        if( $model ) {
            if( $model_id ) {
                return $model::findOrFail($model_id);
            }

            return new $model();
        }

        return null;
    }
}

