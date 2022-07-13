<?php

namespace MAlsafadi\LaravelQueue;

use Carbon\Carbon;
use Illuminate\Container\Container;

/**
 *
 */
class LaravelQueue extends LaravelQueueAbstract
{
    public function __construct(Container $app)
    {
        parent::__construct($app);
        $this->load();
    }

    /**
     * Filter current loaded queues.
     *
     * @param \Closure $callback
     *
     * @return static
     *
     */
    public function filter(\Closure $callback): static
    {
        $this->debug("filter", [ __METHOD__, func_get_args() ]);
        foreach( $this->queues as $name => $queue ) {
            if( ($queue = call_user_func_array($callback, [ $queue, $name ])) !== false ) {
                $this->queues[ $name ] = $queue ?? $this->queues[ $name ];
            } else {
                unset($this->queues[ $name ]);
            }
        }

        return $this;
    }

    /**
     * Filter loaded queue keep only new jobs.
     *
     * @return static
     *
     */
    public function onlyNew(): static
    {
        $this->debug("only new", [ __METHOD__, func_get_args() ]);
        foreach( $this->queues as $name => $queue ) {
            $result_at = data_get($queue, 'result_at');
            if( !is_null($result_at) ) {
                unset($this->queues[ $name ]);
                continue;
            }
        }

        return $this;
    }

    /**
     * Filter loaded queue keep only valid jobs.
     *
     * @return static
     */
    public function onlyValid(): static
    {
        $this->debug("only valid", [ __METHOD__, func_get_args() ]);
        foreach( $this->queues as $name => $queue ) {
            $date = data_get($queue, 'date');
            if( !is_null($date) && !Carbon::parse($date)->isPast() ) {
                unset($this->queues[ $name ]);
                continue;
            }
        }

        return $this;
    }
}
