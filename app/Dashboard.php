<?php

namespace App;

use PDO;
use App\Client;
use App\Office;
use App\LoanAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{

    public static function repaymentTrend($office_id){
        $start = now()->startOfDay()->subDays(6);
        for($x=0;$x<=6;$x++){
            $dates[] = $start->copy()->addDays($x);
        }


        $office = Office::find($office_id);
        // $ids = $office->getLowerOfficeIDS();
        $ids = session('office_list_ids');
        $client_ids = Client::select('client_id')
            ->whereIn('office_id',$ids)
            ->pluck('client_id')
            ->toArray();
        
        $rTables = [];
        $eTables = [];
        
        $ctr = 1;
        $repayments = null;
        $installments = null;
            $ctr = 1;
            
            foreach($dates as $date){
                if($ctr != 1){
                    $eTables[] = DB::table("loan_account_installments")
                    ->select(DB::raw('IFNULL(ROUND(SUM(principal_due+interest)),0) as total'))
                    ->where('date', $date)
                    ->whereExists(function ($q) use ($client_ids) {
                        $q->select('id', 'closed_at', 'client_id');
                        $q->from('loan_accounts');
                        $q->whereNull('closed_at');
                        $q->whereNotNull('disbursed_at');
                        $q->whereNotNull('approved_at');
                        $q->whereColumn('loan_accounts.id', 'loan_account_installments.loan_account_id');
                        $q->whereExists(function ($q2) use ($client_ids) {
                            $q2->select('client_id');
                            $q2->from('clients');
                            $q2->whereColumn('loan_accounts.client_id', 'clients.client_id');
                            $q2->whereIn('client_id', $client_ids);
                        });
                    });
                    $rTables[] = DB::table('loan_account_repayments')
                        ->select(DB::raw('IFNULL(ROUND(SUM(total_paid)),0) as total'))
                        ->where('repayment_date', $date)
                        ->whereExists(function ($q) use ($client_ids) {
                            $q->from('loan_accounts');
                            $q->whereNull('closed_at');
                            $q->whereNotNull('disbursed_at');
                            $q->whereNotNull('approved_at');
                            $q->whereColumn('loan_accounts.id', 'loan_account_repayments.loan_account_id');
                            $q->whereExists(function ($q2) use ($client_ids) {
                                $q2->from('clients');
                                $q2->whereColumn('clients.client_id', 'loan_accounts.client_id');
                                $q2->whereIn('client_id', $client_ids);

                            });
                        });
                }
                $ctr++;
            }

            $expected_repayment = DB::table("loan_account_installments")
                ->select(DB::raw('IFNULL(ROUND(SUM(principal_due+interest)),0) as total'))
                ->where('date', $dates[0])
                ->whereExists(function ($q) use ($client_ids) {
                    $q->select('id', 'closed_at', 'client_id');
                    $q->from('loan_accounts');
                    $q->whereNull('closed_at');
                    $q->whereNotNull('disbursed_at');
                    $q->whereNotNull('approved_at');
                    $q->whereColumn('loan_accounts.id', 'loan_account_installments.loan_account_id');
                    $q->whereExists(function ($q2) use ($client_ids) {
                        $q2->select('client_id');
                        $q2->from('clients');
                        $q2->whereColumn('loan_accounts.client_id', 'clients.client_id');
                        $q2->whereIn('client_id', $client_ids);
                    });
                })
                ->when($eTables,function($q,$eTables){
                    foreach($eTables as $table){
                        $q->unionAll($table);
                    }
                })
                ->pluck('total');

            $actual_repayment = DB::table('loan_account_repayments')
                ->select(DB::raw('IFNULL(ROUND(SUM(total_paid)),0) as total'))
                ->where('repayment_date', $dates[0])
                ->whereExists(function ($q) use ($client_ids) {
                    $q->select('id', 'closed_at', 'client_id');
                    $q->from('loan_accounts');
                    $q->whereNull('closed_at');
                    $q->whereNotNull('disbursed_at');
                    $q->whereNotNull('approved_at');
                    $q->whereColumn('loan_accounts.id', 'loan_account_repayments.loan_account_id');
                    $q->whereExists(function ($q2) use ($client_ids) {
                        $q2->select('client_id');
                        $q2->from('clients');
                        $q2->whereColumn('loan_accounts.id', 'loan_account_repayments.loan_account_id');
                        $q2->whereIn('client_id', $client_ids);

                    });
                })
                ->when($rTables,function($q,$rTables){
                    foreach($rTables as $table){
                        $q->unionAll($table);
                    }
                })
                ->pluck('total');
        // $labels = $dates;
        $labels = [] ;
        collect($dates)->map(function($x) use (&$labels){
            $labels[] = $x->format('d-F');
        });
        return compact('expected_repayment','actual_repayment','labels');
    }

    
    public static function disbursementTrend($office_id){
        $start = now()->startOfDay()->subDays(6);
        for($x=0;$x<=6;$x++){
            $dates[] = $start->copy()->addDays($x);
        }


        $office = Office::find($office_id);
        // $ids = $office->getLowerOfficeIDS();
        $ids = session('office_list_ids');
        $client_ids = Client::select('client_id')
            ->whereIn('office_id',$ids)
            ->pluck('client_id')
            ->toArray();
        
        $ctr=1;
        $dTables = []; //disbursement tables
        $rPTables = []; //repayment Principal tables
        $rITables = []; //repayment Interest tables
        $rTables = [];
        foreach($dates as $date){
            if($ctr!=1){
                $dTables[] = DB::table('loan_accounts')
                    ->where('disbursed',true)
                    ->whereNull('closed_at')
                    ->where('disbursement_date',$date)
                    ->select(DB::raw('IFNULL(ROUND(SUM(amount)),0) as total'))
                    ->whereExists(function($q) use ($client_ids){
                        $q->from('clients');
                        $q->whereIn('client_id',$client_ids);
                    });
                    

                    $rTables[] = DB::table('loan_account_repayments')
                    ->select(DB::raw('IFNULL(ROUND(SUM(principal_paid)),0) as principal_paid, IFNULL(ROUND(SUM(interest_paid)),0) as interest_paid'))
                    ->where('repayment_date', $date)
                    ->whereExists(function ($q) use ($client_ids) {
                        $q->from('loan_accounts');
                        $q->whereNull('closed_at');
                        $q->whereNotNull('disbursed_at');
                        $q->whereNotNull('approved_at');
                        $q->whereColumn('loan_accounts.id', 'loan_account_repayments.loan_account_id');
                        $q->whereExists(function ($q2) use ($client_ids) {
                            $q2->from('clients');
                            $q2->whereColumn('clients.client_id', 'loan_accounts.client_id');
                            $q2->whereIn('client_id', $client_ids);

                        });
                    });
            }
            $ctr++;
        }

        $disbursements = DB::table('loan_accounts')
            ->where('disbursed',true)
            ->whereNull('closed_at')
            ->where('disbursement_date',$dates[0])
            ->select(DB::raw('IFNULL(ROUND(SUM(amount)),0) as total'))
            ->whereExists(function($q) use ($client_ids){
                $q->from('clients');
                $q->whereIn('client_id',$client_ids);
            })
            ->when($dTables, function($q,$dTables){
                foreach($dTables as $table){
                    $q->unionAll($table);
                }
            })
            ->pluck('total');

        $repayments = DB::table('loan_account_repayments')
        ->select(DB::raw('IFNULL(ROUND(SUM(principal_paid)),0) as principal_paid, IFNULL(ROUND(SUM(interest_paid)),0) as interest_paid'))
        ->where('repayment_date', $dates[0])
        ->whereExists(function ($q) use ($client_ids) {
            $q->from('loan_accounts');
            $q->whereNull('closed_at');
            $q->whereNotNull('disbursed_at');
            $q->whereNotNull('approved_at');
            $q->whereColumn('loan_accounts.id', 'loan_account_repayments.loan_account_id');
            $q->whereExists(function ($q2) use ($client_ids) {
                $q2->from('clients');
                $q2->whereColumn('clients.client_id', 'loan_accounts.client_id');
                $q2->whereIn('client_id', $client_ids);
            });
        })
        ->when($rTables, function($q,$rTables){
            foreach($rTables as $table){
                $q->unionAll($table);
            }
        })
        ->get();

        $repayment_interest = $repayments->pluck('interest_paid');
        $repayment_principal = $repayments->pluck('principal_paid');
        $labels = [] ;
        collect($dates)->map(function($x) use (&$labels){
            $labels[] = $x->format('d-F');
        });

        return compact('labels','disbursements','repayment_interest','repayment_principal');
    }
}
