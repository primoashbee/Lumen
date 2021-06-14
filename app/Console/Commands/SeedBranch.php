<?php

namespace App\Console\Commands;

use App\Office;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class SeedBranch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:seed {office_id} {--date=null} {--loan=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds fake data for a target branch';

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
        ini_set('memory_limit', '2048');

        
        if($this->option('date') == 'null'){
            $start_date = Carbon::today()->subDays(rand(0,190));
        }else{
            $start_date = Carbon::parse($this->option('date'));
        }
        if($this->option('loan') =='false'){
            $has_loans = false;
        }else{
            $has_loans = true;
        }
        $level = Office::find($this->argument('office_id'));
        if($level->level == 'branch'){
            $cluster_ids = $level->clusters();
            foreach(Office::whereIn('id',$cluster_ids)->cursor() as $office){
                $this->info('Seeding .... ' . $office->name);
-
                // $office->seed(20, true, $start_date);
                $this->info('Done  ' . $office->name);
            }
        }
        if($level->level == 'unit'){
            $cluster_ids = $level->clusters();
            foreach(Office::whereIn('id',$cluster_ids)->cursor() as $office){
                $this->info('Seeding .... ' . $office->name);
                
                // $office->seed(20, true, $start_date);
                $this->info('Done  ' . $office->name);
            }
        }

        if($level->level == 'cluster'){
            $this->info('Seeding .... ' . $level->name);
            
            // $start_date = now();
            $level->seed(20, $has_loans, $start_date);
            $this->info('Done  ' . $level->name);

        }
        
        return $this->info('Success');
    }
}
