<?php

namespace MAlsafadi\LaravelQueue\Commands;

use MAlsafadi\LaravelQueue\Facades\LaravelQueue;

class StartLaravelQueueCommand extends \Illuminate\Console\Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel:queue:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Laravel Queue Worker.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setAliases([
                              'lqs',
                          ]);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if( !LaravelQueue::isEnabled() || LaravelQueue::isProcessRunning() ) {
            return 0;
        }

        $this->queues()->run();

        return 0;
    }

    public function getQueues(): array
    {
        $queues = $this->argument('queue') ?: [ '.' ];
        $queues = array_map(fn($q) => trim($q), is_array($queues) ? $queues : explode(',', $queues));
        $queues = array_filter($queues, fn($q) => $q && !in_array($q, [ '.' ]));

        return $queues;
    }

    /**
     * @return \MAlsafadi\LaravelQueue\Facades\LaravelQueue
     */
    public function queues()
    {
        return LaravelQueue::load()
                           ->onlyNew()
                           ->onlyValid();
    }
}
