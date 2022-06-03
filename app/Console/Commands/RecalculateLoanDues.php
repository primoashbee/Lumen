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
        
        

        // $accounts = LoanAccount::limit(500)->offset(0);
        
        // foreach($accounts->chunk(100) as $chunk){
        //     foreach ($chunk as $item) {
        //         $item->updateDueInstallments();
        //         $item->updateStatus();
            
        //     }
        // }
        
        try {
            \DB::beginTransaction();
            
            $this->info('Starting....');
            $this->info('Date is ' . now()->toDateString());
            $lai = \DB::table('loan_account_installments');
    
            $loan_accounts_installments_repayments = DB::table('loan_account_installment_repayments')
            ->select('principal_paid','interest_paid','total_paid','penalty_paid','loan_account_installment_id')
            ->leftJoinSub($lai,'loan_account_installment', function($join){
                $join->on('loan_account_installment.id','loan_account_installment_repayments.loan_account_installment_id');
            });
            

            $deposit_accounts_installments_repayments = \DB::table('deposit_to_loan_installment_repayments')
            ->select('principal_paid','interest_paid','total_paid','penalty_paid','loan_account_installment_id')
            ->leftJoinSub($lai,'loan_account_installment', function($join){
                $join->on('loan_account_installment.id','deposit_to_loan_installment_repayments.loan_account_installment_id');
            });

            $payments =  $loan_accounts_installments_repayments->union($deposit_accounts_installments_repayments)
                ->select(
                DB::raw('SUM(principal_paid) as principal_paid'),
                DB::raw('SUM(interest_paid) as interest_paid'),
                DB::raw('SUM(penalty_paid) as penalty_paid'),
                DB::raw('SUM(total_paid) as total_paid'),
                'loan_account_installment_id')
                ->groupBy('loan_account_installment_id');
            
                

                $lai = \DB::table('loan_account_installments')
                ->select(
                    'installment',
                    'amount_due',
                    'date','amortization',
                    'principal','interest',
                    'principal_due',
                    'interest_due',
                    'original_interest',
                    'original_principal',
                    DB::raw('loan_account_installments.id AS installment_id')
                )
                ->leftJoinSub($payments, 'payments', function($join){
                    $join->on('loan_account_installments.id','payments.loan_account_installment_id');
                })
                ->orderBy('installment','asc')
                ->whereDate('date','<=', now())
                ->where('paid',false)
                ->groupBy('loan_account_installments.id')
                ->update(
                        [
                            'interest_due' => DB::raw('round(original_interest - IFNULL(payments.interest_paid,0),2)'),
                            'principal_due' => DB::raw('round(original_principal - IFNULL(payments.principal_paid,0),2)'),
                            'amount_due' => DB::raw('round(round(original_principal - IFNULL(payments.principal_paid,0),2) + 
                            round(original_interest - IFNULL(payments.interest_paid,0),2),2)')
                        ]
                    );
                // ->update(
                //     [
                //         'interest_due' => DB::raw('round(original_interest - IFNULL(payments.interest_paid,0),2)'),
                //         'principal_due' => DB::raw('round(original_principal - IFNULL(payments.principal_paid,0),2)'),
                //         'amount_due' => DB::raw('round(round(original_principal - IFNULL(payments.principal_paid,0),2) + 
                //         round(original_interest - IFNULL(payments.interest_paid,0),2) +
                //         round(penalty - IFNULL(payments.penalty_paid,0),2)
                //         ,2)')
                //     ]
                // );

            $accounts = LoanAccount::active()->chunk(2000, function($loans){
                foreach($loans as $loan){
                    $loan->updateStatus();
                }
            });

            $this->info('Updating ' . $lai . ' accounts. for RECALCULATING LOAN DUES');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            Log::warning($e->getMessage());
        }            
        

        
        
        // $list->update([
        //     'amount_due'=>\DB::raw('round(interest+principal_due,2)'),
        //     'interest_due'=>\DB::raw('interest')
        // ]);
        return $this->info('Done');
    }
}
