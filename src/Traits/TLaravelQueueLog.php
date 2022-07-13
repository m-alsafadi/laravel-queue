<?php

namespace MAlsafadi\LaravelQueue\Traits;

/**
 * @ mixin \MAlsafadi\LaravelQueue\LaravelQueue
 */
trait TLaravelQueueLog
{
    public function getLogDriver(): string
    {
        return $this->config('log_driver', 'stack');
    }

    public function debug(string $message, array $context = [])
    {
        if( $this->debug ) {
            $stack = last(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            if( isset($stack[ 'file' ]) ) {
                $stack[ 'file' ] = str_replace(base_path() . "/", "", $stack[ 'file' ]);
            }
            $file = data_get($stack, 'class', data_get($stack, 'file'));
            $line = data_get($stack, 'line');
            $method = data_get($stack, 'function');
            $type = data_get($stack, 'type');
            logger()->driver($this->getLogDriver())->debug("{$file}{$type}{$method}:{$line} {$message}", $stack);
        }
    }

    protected function info(string $message, array $context = [])
    {
        if( $this->log ) {
            $stack = last(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            if( isset($stack[ 'function' ]) && $stack[ 'function' ] === '__callStatic' ) {
                $line = $stack[ 'line' ] ?? null;
                $stack = last(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3));
                $stack[ 'line' ] = $line ?? ($stack[ 'line' ] ?? null);
            }
            if( isset($stack[ 'file' ]) ) {
                $stack[ 'file' ] = str_replace(base_path() . "/", "", $stack[ 'file' ]);
            }
            if( isset($stack[ 'class' ]) && starts_with($stack[ 'class' ], "MAlsafadi\\LaravelQueue\\") ) {
                $stack[ 'class' ] = str_replace("MAlsafadi\\LaravelQueue\\", "", $stack[ 'class' ]);
            }
            $file = data_get($stack, 'class', data_get($stack, 'file'));
            $line = data_get($stack, 'line');
            $method = data_get($stack, 'function');
            $type = data_get($stack, 'type');
            $logger = logger()->driver($this->getLogDriver())->getLogger();
            $logger->info("{$message}\t{$file}{$type}{$method}:{$line}", $stack);
        }
    }

    public function log(string $message, array $context = [])
    {
        $stack = last(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
        if( isset($stack[ 'function' ]) && $stack[ 'function' ] === '__callStatic' ) {
            $line = $stack[ 'line' ] ?? null;
            $stack = last(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3));
            $stack[ 'line' ] = $line ?? ($stack[ 'line' ] ?? null);
        }
        if( isset($stack[ 'file' ]) ) {
            $stack[ 'file' ] = str_replace(base_path() . "/", "", $stack[ 'file' ]);
        }
        if( isset($stack[ 'class' ]) && starts_with($stack[ 'class' ], "MAlsafadi\\LaravelQueue\\") ) {
            $stack[ 'class' ] = str_replace("MAlsafadi\\LaravelQueue\\", "", $stack[ 'class' ]);
        }
        $file = data_get($stack, 'class', data_get($stack, 'file'));
        $line = data_get($stack, 'line');
        $method = data_get($stack, 'function');
        $type = data_get($stack, 'type');
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = logger()->driver($this->getLogDriver())->getLogger();
        $logger->notice("{$message}\t{$file}{$type}{$method}:{$line}", $stack);
    }
}
