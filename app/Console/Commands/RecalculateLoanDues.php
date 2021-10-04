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
        $list= DB::table('loan_account_installments')
        ->leftJoin('loan_account_installment_repayments', 'loan_account_installment_repayments.loan_account_installment_id', '=', 'loan_account_installments.id')
        ->groupBy('loan_account_installments.id')
        ->select(
            'installment',
            'original_principal',
            'original_interest',
            'date','amortization',
            'principal','interest',
            'principal_due',
            'interest_due',
            'amount_due',
            DB::raw('SUM(loan_account_installment_repayments.interest_paid) AS interest_paid'),
            DB::raw('SUM(loan_account_installment_repayments.principal_paid) AS principal_paid'),
            DB::raw('SUM(loan_account_installment_repayments.total_paid) AS total_paid')
        )
        ->orderBy('installment','asc')
        ->whereDate('date','<=', now())
        ->where('paid',false)
        // ->where('loan_account_id', 102)
        ->update(
            [
                'interest_due' => DB::raw('round(interest-IFNULL(interest_paid,0),2)'),
                'principal_due' => DB::raw('round(principal-IFNULL(principal_paid,0),2)'),
                'amount_due' => DB::raw('round(interest_due+principal_due,2)')
            ]
        );

        $this->info('Starting deduction of CTLP');

        $list= DB::table('loan_account_installments')
        ->leftJoin('deposit_to_loan_installment_repayments', 'deposit_to_loan_installment_repayments.loan_account_installment_id', '=', 'loan_account_installments.id')
        ->groupBy('loan_account_installments.id')
        ->select(
            'installment',
            'original_principal',
            'original_interest',
            'date','amortization',
            'principal','interest',
            'principal_due',
            'interest_due',
            'amount_due',
            DB::raw('SUM(deposit_to_loan_installment_repayments.interest_paid) AS interest_paid'),
            DB::raw('SUM(deposit_to_loan_installment_repayments.principal_paid) AS principal_paid'),
            DB::raw('SUM(deposit_to_loan_installment_repayments.total_paid) AS total_paid')
        )
        ->orderBy('installment','asc')
        ->whereDate('date','<=', now())
        ->where('paid',false)
        ->update(
            [
                'interest_due' => DB::raw('round(interest-IFNULL(interest_paid,0),2)'),
                'principal_due' => DB::raw('round(principal-IFNULL(principal_paid,0),2)'),
                'amount_due' => DB::raw('round(interest_due+principal_due,2)')
            ]
        );

        // $this->info('Updating ' . $list->count() . ' accounts.');
        
        // $list->update([
        //     'amount_due'=>\DB::raw('round(interest+principal_due,2)'),
        //     'interest_due'=>\DB::raw('interest')
        // ]);
        return $this->info('Done');
    }
}
