<?php

namespace App\Console\Commands;

use App\Office;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculatePenalty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:penalty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate Penalty for every past due installment';

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
        try {
            \DB::beginTransaction();
            $date = Carbon::now();
        
            $products = 9;
            $office_id = [1];
            $loan_accounts = \DB::table('loan_accounts');
            $clients = \DB::table('clients');
            $offices = \DB::table('offices');
            $loan = \DB::table('loans');
            $space = " ";
            $list = \DB::table('loan_account_installments')
            ->select(
                \DB::raw('loan_account_installments.id AS installment_id'),
                \DB::raw('SUM(principal_due + interest_due) as total_due'),
                \DB::raw('datediff(CURRENT_TIMESTAMP,date) as penalty_days'),
                \DB::raw("datediff(CURRENT_TIMESTAMP,date) * 0.000142 as per"),
                'date',
                'loan_account_installments.penalty',
                'clients.client_id',
                'loan_accounts.id as la_id',
                'offices.code as level',
                'loan.code as code',
                \DB::raw("concat(clients.firstname, '{$space}',clients.middlename,'{$space}', clients.lastname) as fullname"),
            )
            ->when($office_id, function($q,$data){
                $office_ids = Office::lowerOffices($data);
                $q->whereExists(function($q2) use ($office_ids){
                    $q2->select('office_id','client_id','firstname','lastname')
                            ->from('clients')
                            ->whereIn('office_id',$office_ids)
                            ->whereColumn('clients.client_id','loan_accounts.client_id');
                });
            })
            ->when($products, function($q,$data){
                $q->whereExists(function($q2) use ($data){
                    $q2->from('loan_accounts')
                        ->where('loan_id',$data)
                        ->whereColumn('loan_accounts.id', 'loan_account_installments.loan_account_id');
                });
            })
        ->leftJoinSub($loan_accounts,'loan_accounts', function($join){
                $join->on('loan_accounts.id', 'loan_account_installments.loan_account_id');
            })
            ->leftJoinSub($loan,'loan',function($join){
                $join->on('loan.id','loan_accounts.loan_id');
            })
            ->leftJoinSub($clients, 'clients', function($join){
                $join->on('clients.client_id','loan_accounts.client_id');
            })
            ->leftJoinSub($offices, 'offices', function($join){
                $join->on('offices.id','=','clients.office_id');
            })
            ->groupBy('installment_id')
            ->where('paid',false)
            ->whereRaw('datediff(CURRENT_TIMESTAMP,date) > 0')
            ->update(
                [
                    'loan_account_installments.penalty' => \DB::raw("round(datediff(CURRENT_TIMESTAMP,date) * 0.00142 * loan_account_installments.amount_due,2)")
                ]
            );
            \DB::commit();

            $this->info('penalized');
        } catch (Exception $e) {
            \DB::rollBack();
            Log::warning($e->getMessage());
        }  
    }
}
