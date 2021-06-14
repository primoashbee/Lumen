<?php

namespace App\Console\Commands;

use App\ParMovement;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class ParMovementCompute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'par:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate PAR Movement at the end of day.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $time_start = microtime(true);

        $this->info('Starting....');
        $this->info('Fetching Accounts');
        $start_memory =memory_get_usage();
        $this->info('Memory Usage: '.round(memory_get_usage()/1048576,2).''.' MB');
        

        // sleep(5);


        
        $start = now()->startOfDay()->subDays(7);

        for($x=0;$x<=6;$x++){
            $date  = $start->copy()->addDays($x);
            // $date = now();
            $this->info('Generating for: ' . $date->toDateString());
            // $date = Carbon::parse($this->argument('date'))->toDateString();
            ParMovement::generate($date);
            $this->info('Finished');

        }
        $time_end = microtime(true);
        $this->info('.......');
        $end_memory =memory_get_usage();
        $memory_usage = round(($end_memory - $start_memory)/1048576,2).'MB';
        $runtime = $time_end - $time_start;
        $this->info('Memory Usage: '.$memory_usage.' for this certain command');
        $this->info('Time taken :'.$runtime.' seconds');

    }
}
