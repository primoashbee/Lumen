<?php

namespace App;

use App\Client;
use App\Office;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;


class Dashboard 
{

    public static function repaymentTrend($office_id,$now_only = false){
        
        if($now_only){
            $date = now()->startOfDay();
            $office = Office::find($office_id);
            $ids = Office::lowerOffices($office_id,true,true);
            // $ids = $office->getLowerOfficeIDS();
            // $ids = session('office_list_ids');
            $client_ids = Client::select('client_id')
            ->whereIn('office_id', $ids)
            ->pluck('client_id')
            ->toArray();
        
            $ctr = 1;
            $repayments = null;
            $installments = null;
            $ctr = 1;
     
            $expected_repayment = DB::table("loan_account_installments")
                ->select(DB::raw('IFNULL(ROUND(SUM(principal_due+interest),2),0) as total'))
                ->whereDate('date', \DB::raw("DATE('{$date}')"))
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
                ->get()
                ->pluck('total')->toArray();
                
            $loan_repayments = DB::table('loan_account_repayments')
            ->select(DB::raw('IFNULL(ROUND(SUM(interest_paid+principal_paid),2),0) as total'))
            ->where('reverted',false)
            ->whereDate('repayment_date', \DB::raw("DATE('{$date}')"))
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
                    $q2->whereIn('client_id', $client_ids);
                    $q2->whereColumn('loan_accounts.id', 'loan_account_repayments.loan_account_id');
                });
            });

            $ctlp = \DB::table('deposit_to_loan_repayments')
            ->select(DB::raw('IFNULL(ROUND(SUM(interest_paid+principal_paid),2),0) as total'))
            ->where('reverted',false)
            ->whereDate('repayment_date', \DB::raw("DATE('{$date}')"))
            ->whereExists(function ($q) use ($client_ids) {
                $q->select('id', 'closed_at', 'client_id');
                $q->from('loan_accounts');
                $q->whereNull('closed_at');
                $q->whereNotNull('disbursed_at');
                $q->whereNotNull('approved_at');
                $q->whereColumn('loan_accounts.id', 'deposit_to_loan_repayments.loan_account_id');
                $q->whereExists(function ($q2) use ($client_ids) {
                    $q2->select('client_id');
                    $q2->from('clients');
                    $q2->whereIn('client_id', $client_ids);
                    $q2->whereColumn('loan_accounts.id', 'deposit_to_loan_repayments.loan_account_id');
                });
            });
      
            $actual_repayment = DB::query()->fromSub($ctlp->unionAll($loan_repayments),'subquery')->select(DB::raw('ROUND(SUM(total),2) as total'))->pluck('total')->toArray();
      
            $labels[] = $date->format('d-F');
            return compact('expected_repayment', 'actual_repayment', 'labels');
        }else{
            $start = now()->subDay()->subDays(6);
            
            //get dates 5 days before, last day will be tables will be unioned
            $dates = CarbonPeriod::create(now()->subDay(5),now()->subDay())->toArray();

            $office = Office::find($office_id);
            // $ids = $office->getLowerOfficeIDS();
            $ids = Office::lowerOffices($office_id,true,true);
            // $ids = session('office_list_ids');
            $client_ids = Client::select('client_id')
            ->whereIn('office_id', $ids)
            ->pluck('client_id')
            ->toArray();
        
            
            $eTables = []; //expected
            $rTables = []; //loan_account
            $cTables = []; //ctlp
        
            
            $repayments = null;
            $installments = null;

            foreach ($dates as $date) {

                    
                    $eTables[] = DB::table("loan_account_installments")
                    ->select(DB::raw('IFNULL(ROUND(SUM(principal_due+interest),2),0) as total'))
                    ->whereDate('date', \DB::raw("DATE('{$date}')"))
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
                    
                    $ctlp = DB::table('deposit_to_loan_repayments')
                    ->select(DB::raw('IFNULL(ROUND(SUM(interest_paid+principal_paid),2),0) as total'))
                    ->whereDate('repayment_date', \DB::raw("DATE('{$date}')"))
                    ->where('reverted',false)
                    ->whereExists(function ($q) use ($client_ids) {
                        $q->from('loan_accounts');
                        $q->whereNull('closed_at');
                        $q->whereNotNull('disbursed_at');
                        $q->whereNotNull('approved_at');
                        $q->whereColumn('loan_accounts.id', 'deposit_to_loan_repayments.loan_account_id');
                        $q->whereExists(function ($q2) use ($client_ids) {
                            $q2->from('clients');
                            $q2->whereColumn('clients.client_id', 'loan_accounts.client_id');
                            $q2->whereIn('client_id', $client_ids);
                        });
                    });

                    $repayments = DB::table('loan_account_repayments')
                    ->select(DB::raw('IFNULL(ROUND(SUM(interest_paid+principal_paid),2),0) as total'))
                    ->where('reverted',false)
                    ->whereDate('repayment_date', \DB::raw("DATE('{$date}')"))
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
                    
                    $rTable = $repayments->unionAll($ctlp);
                    $rTables[] = DB::query()->fromSub($rTable,'subquery')->select(DB::raw('ROUND(SUM(total),2) as total'));
            }

            $first_date = now()->subDays(6);
            $expected_repayment = DB::table("loan_account_installments")
                ->select(DB::raw('IFNULL(ROUND(SUM(principal_due+interest),2),0) as total'))
                ->whereDate('date', \DB::raw("DATE('{$first_date}')"))
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
                ->when($eTables, function ($q, $eTables) {
                    foreach ($eTables as $table) {
                        $q->unionAll($table);
                    }
                })
                ->get()
                ->pluck('total')->toArray();

            $ctlp = DB::table('deposit_to_loan_repayments')
            ->select(DB::raw('IFNULL(ROUND(SUM(interest_paid+principal_paid),2),0) as total'))
            ->whereDate('repayment_date', \DB::raw("DATE('{$first_date}')"))
            ->where('reverted',false)
            ->whereExists(function ($q) use ($client_ids) {
                $q->from('loan_accounts');
                $q->whereNull('closed_at');
                $q->whereNotNull('disbursed_at');
                $q->whereNotNull('approved_at');
                $q->whereColumn('loan_accounts.id', 'deposit_to_loan_repayments.loan_account_id');
                $q->whereExists(function ($q2) use ($client_ids) {
                    $q2->from('clients');
                    $q2->whereColumn('clients.client_id', 'loan_accounts.client_id');
                    $q2->whereIn('client_id', $client_ids);
                });
            });

            $repayments = DB::table('loan_account_repayments')
                ->select(DB::raw('IFNULL(ROUND(SUM(interest_paid+principal_paid),2),0) as total'))
                ->where('reverted',false)
                ->whereDate('repayment_date', \DB::raw("DATE('{$first_date}')"))
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
                });
         
            $rTable = $repayments->unionAll($ctlp);
            $actual_repayment = DB::query()->fromSub($rTable,'subquery')->select(DB::raw('ROUND(SUM(total),2) as total'))
            ->when($rTables, function ($q, $rTables) {
                foreach ($rTables as $table) {
                    $q->unionAll($table);
                }
            })
            ->get()
            ->pluck('total')->toArray();

            $labels[] = $first_date->format('d-F');
            collect($dates)->map(function ($x) use (&$labels) {
                $labels[] = $x->format('d-F');
            });

            return compact('expected_repayment', 'actual_repayment', 'labels');
        }
    }

    
    public static function disbursementTrend($office_id,$now_only = false){
        if($now_only){
            $dates = [now()->startOfDay()];
            $office = Office::find($office_id);
            // $ids = $office->getLowerOfficeIDS();
            $ids = session('office_list_ids');
            $client_ids = Client::select('client_id')
            ->whereIn('office_id', $ids)
            ->pluck('client_id')
            ->toArray();
        
            $ctr=1;
            $dTables = []; //disbursement tables
            $rPTables = []; //repayment Principal tables
            $rITables = []; //repayment Interest tables
            $rTables = [];
            $first_date = $dates[0];
            $disbursements = DB::table('loan_accounts')
            ->where('disbursed', true)
            ->whereNull('closed_at')
            ->whereDate('disbursement_date', \DB::raw("DATE('{$first_date}')"))

            ->select(DB::raw('IFNULL(ROUND(SUM(amount)),0) as total'))
            ->whereExists(function ($q) use ($client_ids) {
                $q->from('clients');
                $q->whereIn('client_id', $client_ids);
            })
            ->when($dTables, function ($q, $dTables) {
                foreach ($dTables as $table) {
                    $q->unionAll($table);
                }
            })
            ->get()
            ->pluck('total');

            $loan_repayments = DB::table('loan_account_repayments')
                ->select(DB::raw('IFNULL(ROUND(SUM(principal_paid),2),0) as principal_paid, IFNULL(ROUND(SUM(interest_paid),2),0) as interest_paid'))
                ->where('reverted',false)
                ->whereDate('repayment_date', \DB::raw("DATE('{$first_date}')"))
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
            $ctlp = DB::table('deposit_to_loan_repayments')
                ->select(DB::raw('IFNULL(ROUND(SUM(principal_paid),2),0) as principal_paid, IFNULL(ROUND(SUM(interest_paid),2),0) as interest_paid'))
                ->where('reverted',false)
                ->whereDate('repayment_date', \DB::raw("DATE('{$first_date}')"))
                ->whereExists(function ($q) use ($client_ids) {
                    $q->from('loan_accounts');
                    $q->whereNull('closed_at');
                    $q->whereNotNull('disbursed_at');
                    $q->whereNotNull('approved_at');
                    $q->whereColumn('loan_accounts.id', 'deposit_to_loan_repayments.loan_account_id');
                    $q->whereExists(function ($q2) use ($client_ids) {
                        $q2->from('clients');
                        $q2->whereColumn('clients.client_id', 'loan_accounts.client_id');
                        $q2->whereIn('client_id', $client_ids);
                    });
                });

                $rTable = $loan_repayments->unionAll($ctlp);
                // $repayments = DB::query()->fromSub($rTable,'subquery')->select(DB::raw('ROUND(SUM(principal_paid),2) as principal_paid, ROUND(SUM(interest_paid),2) as interest_paid'))->get();
                $repayments = DB::query()->fromSub($rTable,'subquery')->select(DB::raw('ROUND(SUM(principal_paid),2) as principal_paid, ROUND(SUM(interest_paid),2) as interest_paid'))
                ->when($rTables, function ($q, $rTables) {
                    foreach ($rTables as $table) {
                        $q->unionAll($table);
                    }
                })
                ->get();

            $disbursements = $disbursements;
            $repayment_interest = $repayments->pluck('interest_paid');
            $repayment_principal = $repayments->pluck('principal_paid');
            $labels = [] ;
            collect($dates)->map(function ($x) use (&$labels) {
                $labels[] = $x->format('d-F');
            });

            return compact('labels', 'disbursements', 'repayment_interest', 'repayment_principal');
        }else{
            // $start = now()->subDay()->startOfDay()->subDays(6);
            // for ($x=0;$x<=6;$x++) {
            //     $dates[] = $start->copy()->addDays($x);
            // }
            $start = now()->subDay()->subDays(6);

            $dates = CarbonPeriod::create(now()->subDay(6),now()->subDay())->toArray();


            $office = Office::find($office_id);
            // $ids = $office->getLowerOfficeIDS();
            $ids = Office::lowerOffices($office_id,true,true);
            $client_ids = Client::select('client_id')
            ->whereIn('office_id', $ids)
            ->pluck('client_id')
            ->toArray();
        
            $ctr=1;
            $dTables = []; //disbursement tables
            $rPTables = []; //repayment Principal tables
            $rITables = []; //repayment Interest tables
            $rTables = [];
            foreach ($dates as $date) {
                if($ctr!=1){
                    $dTables[] = DB::table('loan_accounts')
                    ->where('disbursed', true)
                    ->whereNull('closed_at')
                    ->whereDate('disbursement_date', \DB::raw("DATE('{$date}')"))
                    ->select(DB::raw('IFNULL(ROUND(SUM(amount)),0) as total'))
                    ->whereExists(function ($q) use ($client_ids) {
                        $q->from('clients');
                        $q->whereIn('client_id', $client_ids);
                    });
                    

              
                    $loan_repayments = DB::table('loan_account_repayments')
                    ->select(DB::raw('IFNULL(ROUND(SUM(principal_paid)),0) as principal_paid, IFNULL(ROUND(SUM(interest_paid)),0) as interest_paid'))
                    ->where('reverted',false)
                    ->whereDate('repayment_date', \DB::raw("DATE('{$date}')"))
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
                    $ctlp = DB::table('deposit_to_loan_repayments')
                        ->select(DB::raw('IFNULL(ROUND(SUM(principal_paid)),0) as principal_paid, IFNULL(ROUND(SUM(interest_paid)),0) as interest_paid'))
                        ->where('reverted',false)
                        ->whereDate('repayment_date', \DB::raw("DATE('{$date}')"))
                        ->whereExists(function ($q) use ($client_ids) {
                            $q->from('loan_accounts');
                            $q->whereNull('closed_at');
                            $q->whereNotNull('disbursed_at');
                            $q->whereNotNull('approved_at');
                            $q->whereColumn('loan_accounts.id', 'deposit_to_loan_repayments.loan_account_id');
                            $q->whereExists(function ($q2) use ($client_ids) {
                                $q2->from('clients');
                                $q2->whereColumn('clients.client_id', 'loan_accounts.client_id');
                                $q2->whereIn('client_id', $client_ids);
                            });
                        });
                        $rTable = $loan_repayments->unionAll($ctlp);
                        $rTables[] = DB::query()->fromSub($rTable,'subquery')->select(DB::raw('SUM(principal_paid) as principal_paid, SUM(interest_paid) as interest_paid'));
                    }
                    $ctr++;
            }


            $first_date = $dates[0];

            $disbursements = DB::table('loan_accounts')
            ->where('disbursed', true)
            ->whereNull('closed_at')
            ->whereDate('disbursement_date', \DB::raw("DATE('{$first_date}')"))
            ->select(DB::raw('IFNULL(ROUND(SUM(amount)),0) as total'))
            ->whereExists(function ($q) use ($client_ids) {
                $q->from('clients');
                $q->whereIn('client_id', $client_ids);
            })
            ->when($dTables, function ($q, $dTables) {
                foreach ($dTables as $table) {
                    $q->unionAll($table);
                }
            })
            ->pluck('total');

            $loan_repayments = DB::table('loan_account_repayments')
            ->select(DB::raw('IFNULL(ROUND(SUM(principal_paid)),0) as principal_paid, IFNULL(ROUND(SUM(interest_paid)),0) as interest_paid'))
            ->where('reverted',false)
            ->whereDate('repayment_date', \DB::raw("DATE('{$first_date}')"))
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
            $ctlp = DB::table('deposit_to_loan_repayments')
                ->select(DB::raw('IFNULL(ROUND(SUM(principal_paid)),0) as principal_paid, IFNULL(ROUND(SUM(interest_paid)),0) as interest_paid'))
                ->where('reverted',false)
                ->whereDate('repayment_date', \DB::raw("DATE('{$first_date}')"))
                ->whereExists(function ($q) use ($client_ids) {
                    $q->from('loan_accounts');
                    $q->whereNull('closed_at');
                    $q->whereNotNull('disbursed_at');
                    $q->whereNotNull('approved_at');
                    $q->whereColumn('loan_accounts.id', 'deposit_to_loan_repayments.loan_account_id');
                    $q->whereExists(function ($q2) use ($client_ids) {
                        $q2->from('clients');
                        $q2->whereColumn('clients.client_id', 'loan_accounts.client_id');
                        $q2->whereIn('client_id', $client_ids);
                    });
                });
            $rTable = $loan_repayments->unionAll($ctlp);
            $repayments = DB::query()->fromSub($rTable,'subquery')->select(DB::raw('SUM(principal_paid) as principal_paid, SUM(interest_paid) as interest_paid'))
            ->when($rTables, function ($q, $rTables) {
                foreach ($rTables as $table) {
                    $q->unionAll($table);
                }
            });
            
            $disbursements = $disbursements;
            $repayment_interest = $repayments->pluck('interest_paid');
            $repayment_principal = $repayments->pluck('principal_paid');
            $labels = [];
            $labels[] = $first_date->format('d-F');
            collect($dates)->map(function ($x) use (&$labels) {
                $labels[] = $x->format('d-F');
            });

            return compact('labels', 'disbursements', 'repayment_interest', 'repayment_principal');
        }
    }

    public static function parMovement($from, $to, $office_id,$reload = false){
        return ParMovement::dashboardReport($from, $to, $office_id,$reload);
    }

    public static function clientOutreach($office_id,$now_only = false){
        
        $office = Office::find($office_id);
        $ids = Office::lowerOffices($office_id,true,true);
        // $ids = session('office_list_ids');
        // $client_ids = Client::select('client_id')
        //     ->whereIn('office_id',$ids)
        //     ->pluck('client_id')
        //     ->toArray();
        $date = now();
        $installments_without_par = \DB::table('loan_account_installments')
                            ->select(
                                'loan_account_id',
                                \DB::raw('row_number() OVER(partition BY loan_account_id ORDER BY date ASC) AS rowno')
                            )
                            ->whereDate('date','<=',\DB::raw("DATE('{$date}')"))
                            ->where('paid',true);

        $installments_with_par = \DB::table('loan_account_installments')
                            ->select(
                                'loan_account_id',
                                \DB::raw('row_number() OVER(partition BY loan_account_id ORDER BY date ASC) AS rowno')
                            )
                            ->whereDate('date','<=',\DB::raw("DATE('{$date}')"))
                            ->where('paid',false);

        //without par
        $client_without_par = \DB::table('loan_accounts')
        ->select(\DB::raw('count(*) as total'))
        ->joinSub($installments_without_par,'installments',function($join){
            $join->on('installments.loan_account_id','=','loan_accounts.id');
        })
        ->whereExists(function($q) use($ids){
            $q->from('clients')
            ->whereIn('office_id',$ids)
            ->whereColumn('clients.client_id','loan_accounts.client_id');
        })
        ->where('installments.rowno','=',1)
        ->whereNull('loan_accounts.closed_at')
        ->get()->pluck('total');


        //with par
        $client_with_par = \DB::table('loan_accounts')
        ->select(\DB::raw('count(*) as total'))
        ->joinSub($installments_with_par,'installments',function($join){
            $join->on('installments.loan_account_id','=','loan_accounts.id');
        })
        ->whereExists(function($q) use($ids){
            $q->from('clients')
            ->whereIn('office_id',$ids)
            ->whereColumn('clients.client_id','loan_accounts.client_id');
        })
        ->where('installments.rowno','=',1)
        ->whereNull('loan_accounts.closed_at')
        ->get()->pluck('total');


        $client_without_loan = \DB::table('clients')
                    ->select(
                        // '*'
                        \DB::raw('IFNULL(count(*),0) as total')
                    )
                    ->whereNotExists(function($q){
                        $q->select('client_id')
                            ->from('loan_accounts')
                            ->whereNull('closed_at')
                            ->whereColumn('loan_accounts.client_id','=','clients.client_id');
                    })
                    ->whereIn('office_id',$ids)
                    ->get()->pluck('total');
        
        $total = $client_with_par[0] + $client_without_par[0] + $client_without_loan[0];
        $percentages[] = round($client_with_par[0] / $total,2);
        $percentages[] = round($client_without_par[0] / $total,2);
        $percentages[] = round($client_without_loan[0] / $total,2);

        return compact('client_with_par','client_without_par','client_without_loan','percentages');
        
    }

    public static function summary($office_id,$reload = false){
        $office = Office::find($office_id);
        $ids = Office::lowerOffices($office_id,true,true);
        // $ids = $office->getLowerOFficeIDS();
        
        $par_accounts = \DB::table('loan_accounts')
                                ->select(
                                    \DB::raw('SUM(principal_balance) as par_amount, count(*) as total, loan_id')
                                )
                                ->where('status','In Arrears')
                                ->whereNull('closed_at')
                                ->whereExists(function($q) use ($ids){
                                    $q->from('clients')
                                    ->whereIn('client_id',$ids)
                                    ->whereColumn('clients.client_id','loan_accounts.client_id');
                                })
                                ->groupBy('loan_id');
        $accounts = \DB::table('loan_accounts')
                                ->select(
                                    \DB::raw('SUM(principal_balance) as loan_receivable, count(*) as total, loan_id')
                                )
                                ->whereNull('closed_at')
                                ->whereExists(function($q) use ($ids){
                                    $q->from('clients')
                                    ->whereIn('client_id',$ids)
                                    ->whereColumn('clients.client_id','loan_accounts.client_id');
                                })
                                ->groupBy('loan_id');
        $cbu = \DB::table('deposit_accounts');


        $cbu_accounts = \DB::table('loan_accounts')
        ->select(
            \DB::raw('sum(cbu.balance) as balance, loan_id ')
        )
        ->whereNull('loan_accounts.closed_at')
        ->whereExists(function($q) use ($ids){
            $q->from('clients')
            ->whereIn('client_id',$ids)
            ->whereColumn('clients.client_id','loan_accounts.client_id');
        })
        ->leftJoinSub($cbu,'cbu',function($join){
            $join->on('cbu.client_id','=','loan_accounts.client_id');
        })
        ->groupBy('loan_id');
        

        // $loan_cbu = $accounts
                        
        //                 ->leftJoin($cbu,'cbu',function($join){
        //                     $join->on('cbu.client_id','loan_accounts.client_id');
        //                 });
        $without_loan = \DB::table('loan_accounts')
                            ->select(
                                \DB::raw('count(*) as number_of_clients, loan_id')
                            )
                            ->whereExists(function($q) use($ids){
                                $q->from('clients')
                                ->whereIn('office_id',$ids)
                                ->whereColumn('loan_accounts.client_id','clients.client_id');
                            })
                            ->whereNotNull('closed_at')
                            ->groupBy('loan_id');
        
                            
                            
        $summary = \DB::table('loans')
                    ->select(
                        'loans.id','loans.code','loans.name',
                        \DB::raw('IFNULL(par_accounts.par_amount,0) as par_amount, IFNULL(par_accounts.total,0) as par_accounts_total'),
                        \DB::raw('IFNULL(accounts.loan_receivable,0) as loan_receivable, IFNULL(accounts.total,0) as number_of_clients'),
                        \DB::raw("IFNULL(
                            CONCAT(ROUND((IFNULL(par_accounts.par_amount,0)/IFNULL(accounts.loan_receivable,0)) * 100,2))
                            , 0)  as par_ratio "),
                        \DB::raw('IFNULL(ROUND(cbu.balance,2),0) as cbu'),
                        \DB::raw("IFNULL(CONCAT(ROUND((IFNULL(cbu.balance,0)/IFNULL(accounts.loan_receivable,0)) * 100,2)),0) as cbu_lr_ratio"),
                        \DB::raw("IFNULL(without_loans.number_of_clients,0) as without_loans")

                    )
                    ->leftJoinSub($par_accounts,'par_accounts',function($join){
                        $join->on('par_accounts.loan_id','=','loans.id');
                    })
                    ->leftJoinSub($accounts,'accounts',function($join){
                        $join->on('accounts.loan_id','=','loans.id');
                    })
                    ->leftJoinSub($cbu_accounts,'cbu',function($join){
                        $join->on('cbu.loan_id','=','loans.id');
                    })
                    ->leftJoinSub($without_loan,'without_loans',function($join){
                        $join->on('without_loans.loan_id','=','loans.id');
                    })
                    ->get();
        $data = [
            'id'=>0,
            'code'=>'Total',
            'name'=>'Total',
            'number_of_clients'=>0,
            'loan_receivable'=>0,
            'par_amount'=>0,
            'cbu'=>0,
            'cbu_lr_ratio'=>0,
            'par_ratio'=>0,
            'without_loans'=>0
        ];
        
        collect($summary)->map(function($item) use (&$data){
            $data['number_of_clients']+=$item->number_of_clients;
            $data['loan_receivable']+=$item->loan_receivable;
            $data['par_amount']+=$item->par_amount;
            $data['cbu']+=$item->cbu;
            $data['without_loans']+=$item->without_loans;
            // $data['cbu_lr_ratio']+=$item->cbu_lr_ratio;
            // $data['par_ratio']+=$item->cbu;
        });

        $data['cbu_lr_ratio'] = round((($data['cbu'] / $data['loan_receivable']) * 100),2);
        $data['par_ratio'] = round((($data['par_amount'] / $data['loan_receivable']) * 100),2);
        $summary[] = $data;
                        
        return($summary);

    }

    public static function clientTrend($office_id,$reload=false){

        $office = Office::find($office_id);
        $ids = Office::lowerOffices($office_id,true,true);
        // $ids = session('office_list_ids');
        $dates = CarbonPeriod::create(now()->subMonth(2), '1 month', now())->toArray();

        foreach ($dates as $date) {
            
                $sub_new_loans[] = \DB::table('loan_accounts')
                ->select(
                    \DB::raw("count(*) as total, IF(1=1,'{$date->format('F')}',NULL) as month")
                )
                ->whereExists(function ($q) use ($ids) {
                    $q->from('clients')
                    ->whereIn('office_id', $ids)
                    ->whereColumn('clients.client_id', 'loan_accounts.client_id');
                })
                ->where(\DB::raw('MONTHNAME(disbursement_date)'), $date->format('F'));

                $sub_resigned[] = \DB::table('deposit_accounts')
                ->select(
                    \DB::raw("COUNT(DISTINCT(client_id)) as total, IF(1=1,'{$date->format('F')}',NULL) as month")
                )
                ->whereExists(function ($q) use ($ids) {
                    $q->from('clients')
                    ->whereIn('office_id', $ids)
                    ->whereColumn('clients.client_id', 'deposit_accounts.client_id');
                })
                ->where('status','!=','Active')
                ->where(\DB::raw('MONTHNAME(last_transaction_date)'), $date->format('F'));  
            
            
        }

        
        $now = now()->subMonths(3)->format('F');
        $new_loans = \DB::table('loan_accounts')
        ->select(
            \DB::raw("count(*) as total,  IF(1=1,'{$now}', NULL)as month")
        )
        ->whereExists(function ($q) use ($ids) {
            $q->from('clients')
            ->whereIn('office_id', $ids)
            ->whereColumn('clients.client_id', 'loan_accounts.client_id');
        })
        ->where(\DB::raw('MONTHNAME(disbursement_date)'), $now)
        ->when($sub_new_loans, function($q,$data){
            foreach($data as $table){
                $q->unionAll($table);
            }
        })
        ->get()->pluck('total');
        
        $resigned = \DB::table('deposit_accounts')
        ->select(
            \DB::raw("COUNT(DISTINCT(client_id)) as total,  IF(1=1,'{$now}', NULL)as month")
        )
        ->whereExists(function ($q) use ($ids) {
            $q->from('clients')
            ->whereIn('office_id', $ids)
            ->whereColumn('clients.client_id', 'deposit_accounts.client_id');
        })
        ->where('status','!=','Active')
        ->where(\DB::raw('MONTHNAME(last_transaction_date)'), $now)

        ->when($sub_resigned, function($q,$data){
            foreach($data as $table){
                $q->unionAll($table);
            }
        })
        ->get();
        $labels = $resigned->pluck('month');
        $resigned = $resigned->pluck('total');
    return compact('labels','resigned','new_loans');

    }
}
