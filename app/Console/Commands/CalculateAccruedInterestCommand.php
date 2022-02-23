<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\DepositAccount;
use App\PostedAccruedInterest;
use Exception;
use Illuminate\Console\Command;

class CalculateAccruedInterestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deposit:accrue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the accrued interest for the deposit accounts';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time_start = microtime(true);

        $this->info('Starting....');
        $this->info('Fetching Accounts');
        $start_memory =memory_get_usage();
        $this->info('Memory Usage: '.round(memory_get_usage()/1048576,2).''.' MB');
        
       
        try {
            \DB::beginTransaction();
                DepositAccount::accrueInterestAll();
            \DB::commit();
        }
        catch (Exception $e){
            \DB::rollback();
            Log::warning($e->getMessage());
        }
        $time_end = microtime(true);
        $runtime = $time_end - $time_start;

        $this->info('.......');
        $end_memory =memory_get_usage();
        $memory_usage = round(($end_memory - $start_memory)/1048576,2).'MB';
        $this->info('Memory Usage: '.$memory_usage.' for this certain command');
        

        $this->info('Time taken :'.$runtime.' seconds');
        
    }
}
