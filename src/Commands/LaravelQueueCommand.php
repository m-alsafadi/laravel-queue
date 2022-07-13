<?php

namespace MAlsafadi\LaravelQueue\Commands;

use MAlsafadi\LaravelQueue\Facades\LaravelQueue;
use Symfony\Component\Console\Input\InputOption;

/**
 *
 */
class LaravelQueueCommand extends \Illuminate\Console\Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel:queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Laravel Queue Helper.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setAliases([
                              'lq',
                          ]);

        $this->addOption('success', 'S', InputOption::VALUE_OPTIONAL, 'Show only success jobs.', false);
        $this->addOption('failed', 'F', InputOption::VALUE_OPTIONAL, 'Show only failed jobs.', false);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $success = $this->option('success') !== false;
        $failed = $this->option('failed') !== false;

        $is_fail = $success ? true : ($failed ? false : null);
        $queues = LaravelQueue::get(null, $is_fail);

        if( $is_fail ) {
            $title = "Succeed";
        } elseif( $is_fail === false ) {
            $title = "Failed";
        } else {
            $title = "Queue";
        }
        $this->comment("Laravel Queue: {$title} jobs. (" . count($queues) . ")");

        $header = [ "#", "name", "model:id", "job", "valid" ];
        $rows = collect($queues)
            ->values()
            ->map(fn($a, $i) => [
                "#" => $i + 1,
                "name" => $a[ 'name' ] ?? "-",
                "model:id" => ($a[ 'model' ] ?? "") . ":" . ($a[ 'model_id' ] ?? '-') . "",
                "job" => $a[ 'job' ] ?? "-",
                "valid" => $a[ 'date' ] ?? "-",
            ])
            ->all();

        $this->table($header, $rows);

        return 0;
    }
}
