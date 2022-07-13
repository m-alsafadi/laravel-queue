<?php

namespace MAlsafadi\LaravelQueue;

use Carbon\Carbon;
use Illuminate\Container\Container;
use MAlsafadi\LaravelQueue\Facades\LaravelQueue;
use MAlsafadi\LaravelQueue\Traits\TLaravelQueueCache;
use MAlsafadi\LaravelQueue\Traits\TLaravelQueueFile;
use MAlsafadi\LaravelQueue\Traits\TLaravelQueueLog;

/**
 *
 */
abstract class LaravelQueueAbstract
{
    use TLaravelQueueLog,
        TLaravelQueueFile,
        TLaravelQueueCache;

    /**
     * The container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected Container $app;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\FilesystemManager
     */
    protected $filesystem;

    /**
     * The filesystem disk instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $disk;

    /**
     * Config repository.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $configs;

    /**
     * Queues.
     *
     * @var array
     */
    public $queues = [];

    /**
     * @var null
     */
    public static $currentName = null;
    /**
     * @var null
     */
    public static $currentQueue = null;

    public bool $debug = false;
    public bool $log = false;
    public bool $allow_add_executed_job = false;
    public bool $auto_save = true;
    public bool $use_cache = false;
    public bool $human_readable_save = false;
    public bool $single_process = true;

    public string $STORAGE_NAME = 'jobs.json';
    public string $STORAGE_NAME_FAIL = 'failed_jobs.json';
    public string $STORAGE_NAME_SUCCESS = 'success_jobs.json';

    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->configs = $this->app[ 'config' ];
        $this->filesystem = $this->app[ 'filesystem' ];
        $this->disk = $this->disk();
        $this->allow_add_executed_job = $this->config('allow_add_executed_job', false);
        $this->auto_save = $this->config('auto_save', true);
        $this->use_cache = $this->config('use_cache', false);
        $this->human_readable_save = $this->config('human_readable_save', false);
        $this->single_process = $this->config('single_process', true);
        $this->STORAGE_NAME = $this->config('jobs_filename', "jobs.json");
        $this->STORAGE_NAME_FAIL = $this->config('failed_jobs_filename', "failed_jobs.json");
        $this->STORAGE_NAME_SUCCESS = $this->config('success_jobs_filename', "success_jobs.json");
    }

// region: Queue

    /**
     * Run all queues.
     *
     * @param array $only
     *
     * @return static
     *
     */
    public function run()
    {
        if( !static::isEnabled() ) {
            return $this;
        }

        $this->debug("run", [ __METHOD__, func_get_args() ]);
        $this->info("Run Queue: " . $this->count(), []);
        foreach( $this->queues as $name => &$queue ) {
            $job = $this->initJob($name, $queue);
            if( is_null($job) ) {
                $this->info("Skip Job: [$name]", $queue);
                continue;
            }
            $this->info("Start Job: [$name]", $queue);

            // fail false throw
            // success true null
            $success = false;
            try {
                $results = $this->callJob($job);
                $success = $results || is_null($results);
            } catch(\Exception $exception) {
                $results = $exception->getMessage();
                $success = false;
            }

            $this->info("Result Job [$name]: " . print_r(array_wrap($results), true), compact('results'));
            // register response
            $queue[ 'result' ] = $results;
            $queue[ 'result_at' ] = now();

            $this->addOrUpdate($name, $queue, null);

            $this->move($name, null, $success);
        }
        unset($queue);

        return $this;
    }

    /**
     * Load all queue.
     *
     * @return static
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function load(): static
    {
        $this->debug("load", [ __METHOD__, func_get_args() ]);
        $queue = [];
        if( !$this->exists(null) ) {
            $this->queues = $queue;
            $this->put($this->queues, null);
        }

        $this->queues = $this->get(null, null);

        return $this;
    }

    /**
     * Current Queue count
     *
     * @return int
     */
    public function count()
    {
        $this->debug("count", [ __METHOD__, func_get_args() ]);

        return count($this->queues);
    }

    /**
     * Returns current queue.
     *
     * @return array
     */
    public function all(): array
    {
        $this->debug("all", [ __METHOD__, func_get_args() ]);

        return ($this->queues ?: []);
    }
// endregion: Queue

    /**
     * Check if the package is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->config('enabled', true);
    }

    /**
     * Get from package config.
     *
     * @param $key
     * @param $default
     *
     * @return array|mixed
     */
    public function config($key, $default = null)
    {
        $key = $key ? ".{$key}" : "";

        return $this->configs->get("laravel-queue{$key}", $default);
    }

    /**
     * Check if the file exists.
     *
     * @param bool|null $is_fail
     *
     * @return bool
     */
    public function exists(?bool $is_fail = null): bool
    {
        $this->debug("exists", [ __METHOD__, func_get_args() ]);
        if( $this->use_cache ) {
            return $this->hasCache($is_fail);
        }

        return $this->disk()->exists($this->getFilename($is_fail));
    }

    /**
     * Check if the file exists (private).
     *
     * @return bool
     */
    public function dataExists(): bool
    {
        $this->debug("data exists", [ __METHOD__, func_get_args() ]);
        if( $this->use_cache ) {
            return $this->hasDataCache();
        }

        return $this->disk()->exists("data.json");
    }

    /**
     * Save the given data.
     *
     * @param $value
     *
     * @return $this
     */
    public function put($value = null, ?bool $is_fail = null)
    {
        $this->debug("put", [ __METHOD__, func_get_args() ]);

        return $this->use_cache ? $this->putCache($value, $is_fail) : $this->putDisk($value, $is_fail);
    }

    /**
     * Update queue in storage by the given name.
     *
     * @param string|int $key
     * @param array      $values
     *
     * @return bool
     */
    public function update($key, array $values)
    {
        $this->debug("update: [$key]", [ __METHOD__, func_get_args() ]);
        $queues = $this->get();
        if( !isset($queues[ $key ]) ) {
            return false;
        }

        $queue =  &$queues[ $key ];
        foreach( $values as $_key => $_value ) {
            $queue[ $_key ] = $_value;
        }
        unset($queue);
        $this->put($queues, null);

        if( isset($this->queues[ $key ]) ) {
            $this->queues[ $key ] = $values;
        }

        return true;
    }

    /**
     * Get Queue from storage.
     *
     * @param string|null $key
     * @param bool|null   $is_fail
     *
     * @return $this|array|mixed
     */
    public function get(?string $key = null, ?bool $is_fail = null)
    {
        $this->debug("get: [$key]", [ __METHOD__, func_get_args() ]);
        $data = $this->use_cache ? $this->getCache($is_fail) : $this->getDisk($is_fail);

        if( !is_null($key) ) {
            return data_get($data, $key);
        }

        return $data;
    }

    /**
     * Remove queue from storage by its name.
     *
     * @param           $name
     * @param bool|null $is_fail
     *
     * @return $this
     */
    public function remove($name, ?bool $is_fail = null)
    {
        $this->debug("remove: [$name]", [ __METHOD__, func_get_args() ]);
        $data = $this->get(null, $is_fail);
        unset($data[ $name ]);

        return $this->put($data, $is_fail);
    }

    /**
     * Move queue from storage to another depending on status.
     *
     * @param string|int $name
     * @param bool|null  $from
     * @param bool|null  $to
     *
     * @return static
     */
    public function move($name, ?bool $from = null, ?bool $to = null): static
    {
        $this->debug("move: [$name]", [ __METHOD__, func_get_args() ]);
        $fromData = $this->get(null, $from);
        $data = array_pull($fromData, $name);
        $this->put($fromData, $from);

        $toData = $this->get(null, $to);
        $toData[ $name ] = $data;
        $this->put($toData, $to);

        return $this;
    }

    /**
     * Add new queue or update existing one by its name.
     *
     * @param array|string|null|mixed $value
     * @param bool|null               $is_fail
     *
     * @return $this
     */
    public function addOrUpdate($name, $value, ?bool $is_fail = null)
    {
        $this->debug("add or update: [$name]", [ __METHOD__, func_get_args() ]);
        $data = $this->get(null, $is_fail);
        $data[ $name ] = $value;

        return $this->put($data, $is_fail);
    }

    /**
     * Add job to queue.
     *
     * @param \Illuminate\Database\Eloquent\Model                         $model
     * @param string|\MAlsafadi\LaravelQueue\Jobs\AbstractLaravelQueueJob $job
     * @param \DateTime|null                                              $valid_at
     * @param array|null                                                  $arguments
     *
     * @return $this
     * @throws \Throwable
     */
    public function addJob(\Illuminate\Database\Eloquent\Model $model, $job, ?\DateTime $valid_at = null, ?array $arguments = null)
    {
        throw_if(empty($job), "Missing job class!");
        $name = $this->fixName(get_class($model) . ":{$model->id}");
        if( !$this->allow_add_executed_job && static::isInCurrentQueue($name, $job) ) {
            return $this;
        }

        $this->debug("add: [$job]", [ __METHOD__, func_get_args() ]);

        $arguments = $arguments ? array_wrap(value($arguments)) : $arguments;
        $name = $name ?: ($arguments && count($arguments) ? head($arguments) : uniqid());
        $data = [
            'name' => $name,
            'job' => $job,
            'model' => $model ? get_class($model) : null,
            'model_id' => $model->id,
            'arguments' => $arguments,
            'date' => $valid_at,
            'result' => null,
            'result_at' => null,
            'created_at' => now(),
        ];

        $this->info("New Job [$name]: " . print_r($data, true), $data);

        return $this->addOrUpdate($name, $data);
    }

    /**
     * Prepare job to execute it.
     *
     * @param $name
     * @param $queue
     *
     * @return \MAlsafadi\LaravelQueue\Jobs\AbstractLaravelQueueJob|null
     * @throws \Throwable
     */
    public function initJob($name, $queue)
    {
        $this->debug("init: [$name]", [ __METHOD__, func_get_args() ]);
        $jobClass = data_get($queue, 'job', false);
        throw_if(!$jobClass || !class_exists($jobClass), "Class Not Exists! [{$jobClass}]");

        $arguments = array_wrap(data_get($queue, 'arguments', [ $name ]));
        $name = $arguments[ 0 ] ?? $name;

        if( isset($arguments[ 1 ]) && $arguments[ 1 ] ) {
            $arguments[ 1 ] = Carbon::parse($arguments[ 1 ]);
        }

        if( count($arguments) > 2 ) {
            $arguments = slice($arguments, 0, 2);
        }

        static::setCurrentQueue();
        /** @var \MAlsafadi\LaravelQueue\Jobs\AbstractLaravelQueueJob $jobClass */
        $job = $jobClass::makeLaravelQueueInstance($arguments, $name, $queue);
        $job && static::setCurrentQueue($name, $queue);

        return $job;
    }

    /**
     * Execute Job.
     *
     * @param \MAlsafadi\LaravelQueue\Jobs\AbstractLaravelQueueJob $job
     *
     * @return mixed
     */
    public function callJob($job)
    {
        $this->debug("callJob", [ __METHOD__, func_get_args() ]);
        $results = $job->callHandler();
        static::setCurrentQueue();
        $this->debug("callJobResults: {$results}", [ __METHOD__, func_get_args() ]);

        return $results;
    }

    /**
     * Check if the given name & job currently executing.
     *
     * @param                                                                  $name
     * @param string|\MAlsafadi\LaravelQueue\Jobs\AbstractLaravelQueueJob|null $job
     *
     * @return bool
     */
    public static function isInCurrentQueue($name, $job = null)
    {
        if( static::$currentName !== $name ) {
            return false;
        }
        $currentClass = data_get(static::$currentQueue, 'job');

        return $currentClass && $job && $currentClass === $job;
    }

    /**
     * Returns current executing job.
     *
     * @return null[]
     */
    public static function getCurrentQueue()
    {
        return [
            static::$currentName,
            data_get(static::$currentQueue, 'class'),
        ];
    }

    /**
     * Set current executing job.
     *
     * @param $name
     * @param $queue
     *
     * @return void
     */
    public static function setCurrentQueue($name = null, $queue = null)
    {
        static::$currentName = $name;
        static::$currentQueue = $queue;
    }

    /**
     * Check if the given date is past.
     *
     * @param string|\DateTime|Carbon $date
     *
     * @return bool|null
     */
    public static function isPast($date): ?bool
    {
        return $date && ($date = Carbon::parse($date)) && $date->isPast();
    }

    /**
     * Check if the givin date is past or throw error.
     *
     * @param string|\DateTime|Carbon $date
     * @param string                  $exception_message
     *
     * @return bool
     * @throws \Throwable
     */
    public static function checkDateTime($date, bool $allow_null = false, string $exception_message = "date not past yet"): bool
    {
        throw_if(( !$allow_null && is_null($date)) || !static::isPast($date), $exception_message);

        return true;
    }

    public function fixName(\Closure|string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9]+/', '', value($name));
    }

    /**
     * Save the given data (private).
     *
     * @param $value
     *
     * @return $this
     */
    public function putData($value = null)
    {
        $this->debug("put data", [ __METHOD__, func_get_args() ]);

        return $this->use_cache ? $this->putDataCache($value) : $this->putDataDisk($value);
    }

    /**
     * Get Queue from storage (private).
     *
     * @return array
     */
    public function getData(): array
    {
        $this->debug("get data", [ __METHOD__, func_get_args() ]);

        return array_wrap($this->use_cache ? $this->getDataCache() : $this->getDataDisk());
    }

    public function isProcessRunning(): bool
    {
        if( !$this->single_process ) {
            return false;
        }

        $pid = data_get(static::getData(), 'pid');
        $isRunning = false;

        if( $pid ) {
            if( strncasecmp(PHP_OS, "win", 3) == 0 ) {
                $out = [];
                exec("TASKLIST /FO LIST /FI \"PID eq $pid\"", $out);
                if( count($out) > 1 ) {
                    $isRunning = true;
                }
            } elseif( @posix_kill(intval($pid), 0) ) {
                $isRunning = true;
            }
        }

        if( !$isRunning ) {
            static::putData([ 'pid' => getmypid() ]);
        }

        return $isRunning;
    }
}
