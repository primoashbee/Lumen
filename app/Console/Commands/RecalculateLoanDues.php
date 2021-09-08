<?php

namespace App\Console\Commands;

use App\LoanAccount;
use Illuminate\Console\Command;

class RecalculateLoanDues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate loan dues based on the current date. Call this command on 12mn';

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
     * @return mixed
     */
    public function handle()
    {
        // $accounts = LoanAccount::all();
      
        $accounts = LoanAccount::active();

        // $accounts = LoanAccount::limit(500)->offset(0);
        
        // foreach($accounts->chunk(100) as $chunk){
        //     foreach ($chunk as $item) {
        //         $item->updateDueInstallments();
        //         $item->updateStatus();
                
        //     }
        // }

        foreach($accounts as $item){
            $item->updateStatus();
        }

        $this->info('Starting....');
        $this->info('Date is ' . now()->toDateString());
        $list = \DB::table('loan_account_installments')
            ->whereDate('date','<=', now())
            ->where('paid',false);

        $this->info('Updating ' . $list->count() . ' accounts.');
        
        $list->update([
            'amount_due'=>\DB::raw('round(interest+principal_due,2)'),
            'interest_due'=>\DB::raw('interest')
        ]);
        return $this->info('Done');
    }
}
