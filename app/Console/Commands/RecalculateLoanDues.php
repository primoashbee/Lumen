<?php

namespace App\Console\Commands;

use App\LoanAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
      
        $accounts = LoanAccount::active()->get();

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
        $lai = DB::table('loan_account_installments');
        $loan_accounts_installments_repayments = DB::table('loan_account_installment_repayments')
        ->select('principal_paid','interest_paid','total_paid','loan_account_installment_id')
        ->leftJoinSub($lai,'loan_account_installment', function($join){
            $join->on('loan_account_installment.id','loan_account_installment_repayments.loan_account_installment_id');
        });
        

        $deposit_accounts_installments_repayments = DB::table('deposit_to_loan_installment_repayments')
        ->select('principal_paid','interest_paid','total_paid','loan_account_installment_id')
        ->leftJoinSub($lai,'loan_account_installment', function($join){
            $join->on('loan_account_installment.id','deposit_to_loan_installment_repayments.loan_account_installment_id');
        });

        $payments =  $loan_accounts_installments_repayments->unionAll($deposit_accounts_installments_repayments);

        $lai = DB::table('loan_account_installments')
        ->select(
            'installment',
            'amount_due',
            'date','amortization',
            'principal','interest',
            'principal_due',
            'interest_due',
            'original_interest',
            'original_principal',
            DB::raw('loan_account_installments.id AS installment_id'),
            DB::raw('SUM(payments.principal_paid) AS total_principal_paid'),
            DB::raw('SUM(payments.interest_paid) AS total_interest_paid'),
        )
        ->leftJoinSub($payments, 'payments', function($join){
            $join->on('loan_account_installments.id','payments.loan_account_installment_id');
        })
        ->groupBy('installment_id')
        ->orderBy('installment','asc')
        ->whereDate('date','<=', now())
        ->where('paid',false)
        ->update(
            [
                'interest_due' => DB::raw('round(original_interest - IFNULL(payments.interest_paid,0),2)'),
                'principal_due' => DB::raw('round(original_principal - IFNULL(payments.principal_paid,0),2)'),
                'amount_due' => DB::raw('round(interest_due+principal_due,2)')
            ]
        );

        $this->info('Updating ' . $lai . ' accounts.');
        
        // $list->update([
        //     'amount_due'=>\DB::raw('round(interest+principal_due,2)'),
        //     'interest_due'=>\DB::raw('interest')
        // ]);
        return $this->info('Done');
    }
}
