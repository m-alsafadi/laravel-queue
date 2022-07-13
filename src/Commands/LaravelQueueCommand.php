<?php

namespace MAlsafadi\LaravelQueue\Commands;

use Carbon\Carbon;
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

        $this->addOption('count', 'C', InputOption::VALUE_NONE, 'Print count only.', null);
        $this->addOption('queued', 'Q', InputOption::VALUE_NEGATABLE, 'Show queues jobs.', false);
        $this->addOption('success', 'S', InputOption::VALUE_NEGATABLE, 'Show success jobs.', false);
        $this->addOption('failed', 'F', InputOption::VALUE_NEGATABLE, 'Show failed jobs.', false);
        $this->addOption('asc', 'A', InputOption::VALUE_NONE, 'Sort by asc.', null);
        $this->addOption('humans', 'H', InputOption::VALUE_NONE, 'Dates/Columns readable by humans.', null);
        $this->addOption('sort', 'O', InputOption::VALUE_OPTIONAL, 'Sort by.', null);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = $this->option('count');

        $queued = $this->option('queued') ?? false;
        $success = $this->option('success') ?? false;
        $failed = $this->option('failed') ?? false;

        if( !$queued && !$success && !$failed ) {
            $queued = true;
        }

        $asc = $this->option('asc');
        $humans = $this->option('humans');
        $sort = $this->option('sort');

        $header = [
            [ "#", "name", "model:model_id", "job", "result", "result_at", "date", "created_at" ],
            [ "#", "Name", "Model:Id", "Job", "Result", "Result Date", "Valid At", "Created At" ],
        ];

        $types = [];
        if( $queued ) {
            $types[] = null;
        }
        if( $success ) {
            $types[] = true;
        }
        if( $failed ) {
            $types[] = false;
        }

        !$count && $this->comment("Sort by: " . ($sort ?: "Any") . ", " . ($asc ? "ASC" : "DESC") . ".");
        $total = 0;
        foreach( $types as $type ) {
            $is_fail = $type;
            $queues = LaravelQueue::get(null, $is_fail);
            !$count && $this->comment("(" . count($queues) . ") {$this->getType($is_fail)} jobs.");

            $rows = $this->getResults($queues, $sort, $asc, $humans);
            $total += count($rows);
            !$count && $this->table($header[ $humans ], $rows);
        }
        !$count && $this->comment("Total: {$total}");

        $count && print($total);

        return 0;
    }

    public function getResults(array $queues, ?string $sort_by = null, bool $asc, bool $humans = false, int $serial = 0): array
    {
        $timeMethod = $humans ? "shortRelativeToOtherDiffForHumans" : "toDateTimeString";
        $sort_by ??= '#';

        return collect($queues)
            ->sortBy(fn($r) => $r[ $sort_by ] ?? $r[ 'result_at' ] ?? $r[ 'valid_at' ] ?? $r[ 'date' ] ?? $r[ 'name' ], SORT_REGULAR, !$asc)
            ->values()
            ->map(fn($a, $i) => [
                "#" => $i + $serial + 1,
                $humans ? "Name" : "name" => $a[ 'name' ] ?? "-",
                $humans ? "Model:Id" : "model:model_id" => ($a[ 'model' ] ?? "") . ":" . ($a[ 'model_id' ] ?? '-') . "",
                $humans ? "Job" : "job" => $a[ 'job' ] ?? "-",
                $humans ? "Result" : "result" => ($a[ 'result' ] ?? "-") ?: "0",
                $humans ? "Result Date" : "result_at" => $a[ 'result_at' ] ? Carbon::parse($a[ 'result_at' ])->{$timeMethod}() : "-",
                $humans ? "Valid At" : "date" => $a[ 'date' ] ? Carbon::parse($a[ 'date' ])->{$timeMethod}() : "-",
                $humans ? "Created At" : "created_at" => $a[ 'created_at' ] ? Carbon::parse($a[ 'created_at' ])->{$timeMethod}() : "-",
            ])
            ->all();
    }

    public function getType(?int $type = null): string
    {
        $types = [
            null => "Queued",
            "Failed",
            "Succeed",
        ];

        return $types[ $type ];
    }
}
