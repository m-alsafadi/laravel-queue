<?php

namespace MAlsafadi\LaravelQueue\Commands;

use Symfony\Component\Console\Input\InputArgument;

class NewLaravelQueueJobCommand extends \Illuminate\Console\Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel:queue:job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create New Laravel Queue Job.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setAliases([
                              'lqj',
                          ]);
        $this->addArgument('name', InputArgument::REQUIRED, 'Job Name.', null);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $jobName = $this->argument('name');

        $tpl_path = __DIR__ . '/../../Jobs/Job.php';
        $tpl = file_get_contents($tpl_path);
        $tpl = str_ireplace("class Job", "class {$jobName}", $tpl);

        if( file_put_contents($jobPath = app_path("Jobs/{$jobName}.php"), $tpl) ) {
            $this->info("File: {$jobPath} created.");
        } else {
            $this->error("File: {$jobPath} can not be created.");
        }

        return 0;
    }
}
