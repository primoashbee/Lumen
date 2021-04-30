<?php

namespace App;

use App\Office;
use App\Transaction;
use Illuminate\Support\FacadesDB;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    public static function disbursement($data, $paginated = true){
        $space = " ";
        $offices = DB::table('offices');
        if(!array_key_exists('per_page', $data)){
            $data['per_page'] = 20;
        }
 
        $is_summarized = $data['is_summarized'];
       
        $clients = DB::table('clients');
        $loans = DB::table('loans');
        $users = DB::table('users');
        $offices = DB::table('offices');

        $select = [
            'clients.client_id',
            DB::raw("CONCAT(clients.firstname,'{$space}',clients.lastname) as fullname"),
            'loans.id',
            'loans.code',
            'loan_accounts.principal',
            'loan_accounts.interest',
            'loan_accounts.total_loan_amount',
            'loan_accounts.number_of_months',
            'loan_accounts.number_of_installments',
            'loans.installment_method',
            'loan_accounts.disbursed_amount',
            'loan_accounts.total_deductions',
            'loan_accounts.disbursement_date',
            'loan_accounts.first_payment_date',
            'loan_accounts.last_payment_date',
            'loan_accounts.interest_rate',
            'loan_accounts.notes',
            'offices.code as office_level',
            DB::raw("CONCAT(users.firstname,'{$space}',users.lastname) as disbursed_by"),

        ];
        if($is_summarized){
            $select = [
                DB::raw('offices.code as office_level'),
                DB::raw('loans.code as loan_type'),
                DB::raw('count(loan_accounts.id) as number_of_disbursements'),
                DB::raw('sum(loan_accounts.principal) as principal'),
                DB::raw('sum(loan_accounts.interest) as interest'),
                DB::raw('sum(loan_accounts.total_loan_amount) as total_loan_amount'),
                DB::raw('sum(loan_accounts.disbursed_amount) as disbursed_amount'),
                DB::raw('sum(loan_accounts.total_deductions) as total_deductions'),
            ];
        }
        $list = DB::table('loan_accounts')
                ->select($select)
                ->when($data['from_date'], function($q) use ($data){
                    $q->when($data['to_date'],function($q2) use($data){
                        $q2->whereBetween('disbursement_date',[$data['from_date'], $data['to_date']]);
                    });
                    
                })
                ->when($data['office_id'], function($q, $data){
                    $q->whereExists(function($q2) use($data){
                        $ids = Office::find($data)->getLowerOfficeIDS();
                        $q2->select('office_id','client_id','firstname','lastname')
                        ->from('clients')
                        ->whereIn('office_id',$ids)
                        ->whereColumn('clients.client_id','loan_accounts.client_id');
                    });
                })
                ->when($data['users'], function($q, $data){
                    $q->whereIn('disbursed_by',$data);
                })
                ->when($is_summarized, function($q,$data){
                    if ($data) {
                        $q->groupBy('clients.office_id','loan_accounts.loan_id');
                    }
                })
                ->leftJoinSub($clients,'clients',function($join){
                    $join->on('clients.client_id','loan_accounts.client_id');
                })
                ->leftJoinSub($offices,'offices',function($join){
                    $join->on('offices.id','clients.office_id');
                })
                ->leftJoinSub($loans,'loans',function($join){
                    $join->on('loans.id','loan_accounts.loan_id');
                })
                ->leftJoinSub($users,'users',function($join){
                    $join->on('users.id','loan_accounts.disbursed_by');
                });
            if($is_summarized){
                $sum = DB::query()->fromSub(function($query) use ($list){
                    $query->from($list);
                },'all');
                $summary = [
                    'number_of_disbursements'=> $sum->sum('number_of_disbursements'),
                    'interest'=>$sum->sum('interest'),
                    'principal'=>$sum->sum('principal'),
                    'total_loan'=>$sum->sum('total_loan_amount'),
                    'disbursed'=>$sum->sum('disbursed_amount'),
                    'deductions'=>$sum->sum('total_deductions'),
                ];
            }else{
                $summary = [
                    'number_of_accounts'=> $list->count(),
                    'interest'=>$list->sum('interest'),
                    'principal'=>$list->sum('principal'),
                    'total_loan'=>$list->sum('total_loan_amount'),
                    'disbursed'=>$list->sum('disbursed_amount'),
                    'deductions'=>$list->sum('total_deductions'),
                ];
            }   
        
        
        $data =  $paginated  ? $list->paginate($data['per_page']) : $list;
        return compact('data','summary','is_summarized');

    }
    public static function repayment($data, $paginated = true){
        $space = " ";

        if(!array_key_exists('per_page', $data)){
            $data['per_page'] = 20;
        }
        $is_summarized = $data['is_summarized'];

        $payment_methods = DB::table('payment_methods');
        $loans = DB::table('loans');
        $users = DB::table('users');
        $clients = DB::table('clients');
        $offices = DB::table('offices');
        $loan_accounts = DB::table('loan_accounts');
        $transactions = DB::table('transactions');
        $deposit_accounts = DB::table('deposit_accounts');
        $deposits = DB::table('deposits');

        if($is_summarized){
            $lar_select = [
                DB::raw('offices.code as office_code'),
                DB::raw('COUNT(loan_account_repayments.id) as number_of_repayments'),
                DB::raw('loans.code as loan_code'),
                DB::raw('payment_methods.name as payment_method_name'),
                DB::raw('SUM(loan_account_repayments.principal_paid) as principal_paid'),
                DB::raw('SUM(loan_account_repayments.interest_paid) as interest_paid'),
                DB::raw('SUM(loan_account_repayments.total_paid) as total_paid'),
            ];
            $dtlr_select = [
                DB::raw('offices.code as office_code'),
                DB::raw('COUNT(deposit_to_loan_repayments.id) as number_of_repayments'),
                DB::raw('loans.code as loan_code'),
                DB::raw('payment_methods.name as payment_method_name'),
                DB::raw('SUM(deposit_to_loan_repayments.principal_paid) as principal_paid'),
                DB::raw('SUM(deposit_to_loan_repayments.interest_paid) as interest_paid'),
                DB::raw('SUM(deposit_to_loan_repayments.total_paid) as total_paid'),
            ];
        }else{
            $lar_select = [
                DB::raw('offices.code as office_code'),
                DB::raw('clients.client_id as client_id'),
                DB::raw("CONCAT(clients.firstname, '{$space}', clients.lastname) as client_name"),
                DB::raw('loans.code as loan_code'),
                DB::raw('loan_account_repayments.loan_account_id as loan_account_id'),
                DB::raw('loan_account_repayments.principal_paid as principal_paid'),
                DB::raw('loan_account_repayments.interest_paid as interest_paid'),
                DB::raw('loan_account_repayments.total_paid as total_paid'),
                DB::raw('loan_account_repayments.repayment_date as repayment_date'),
                DB::raw('payment_methods.name as payment_method_name'),
                DB::raw("CONCAT(users.firstname, '{$space}', users.lastname) as paid_by"),
                DB::raw('loan_account_repayments.created_at as timestamp'),
            ];
            $dtlr_select = [
                DB::raw('offices.code as office_code'),
                DB::raw('clients.client_id as client_id'),
                DB::raw("CONCAT(clients.firstname, '{$space}', clients.lastname) as client_name"),
                DB::raw('loans.code as loan_code'),
                DB::raw('deposit_to_loan_repayments.loan_account_id as loan_account_id'),
                DB::raw('deposit_to_loan_repayments.principal_paid as principal_paid'),
                DB::raw('deposit_to_loan_repayments.interest_paid as interest_paid'),
                DB::raw('deposit_to_loan_repayments.total_paid as total_paid'),
                DB::raw('deposit_to_loan_repayments.repayment_date as repayment_date'),
                DB::raw("CONCAT(payment_methods.name,' - ', deposits.product_id) as payment_method_name"),
                DB::raw("CONCAT(users.firstname, '{$space}', users.lastname) as paid_by"),
                DB::raw('deposit_to_loan_repayments.created_at as timestamp'),
            ];            
        }
        
        $loan_account_repayments = DB::table('loan_account_repayments')
                                        ->select(
                                           $lar_select
                                        )
                                        ->when($data['office_id'], function($q) use ($data){
                                            $office_ids = Office::lowerOffices($data['office_id'],true,true);
                                            $q->whereExists(function($q2) use ($office_ids){
                                                $client_ids = DB::table('clients')->select('client_id')->whereIn('office_id',$office_ids)->pluck('client_id')->toArray();
                                                $q2->from('loan_accounts')
                                                    ->whereIn('client_id',$client_ids)
                                                    ->whereColumn('loan_account_repayments.loan_account_id','loan_accounts.id');
                                            });
                                        })
                                        ->when($data['from_date'], function($q) use ($data){
                                            $q->when($data['to_date'],function($q2) use($data){
                                                $q2->whereBetween('loan_account_repayments.repayment_date',[$data['from_date'], $data['to_date']]);
                                            }); 
                                        })
                                        ->when($data['users'], function($q, $data){
                                            $q->whereIn('paid_by',$data);
                                        })
                                        ->leftJoinSub($loan_accounts,'loan_accounts',function($join){
                                            $join->on('loan_accounts.id','loan_account_repayments.loan_account_id');
                                        })
                                        ->leftJoinSub($loans,'loans',function($join){
                                            $join->on('loans.id','loan_accounts.loan_id');
                                        })
                                        ->leftJoinSub($clients,'clients',function($join){
                                            $join->on('clients.client_id','loan_accounts.client_id');
                                        })
                                        ->leftJoinSub($offices,'offices',function($join){
                                            $join->on('offices.id','clients.office_id');
                                        })
                                        ->leftJoinSub($payment_methods,'payment_methods',function($join){
                                            $join->on('payment_methods.id','loan_account_repayments.payment_method_id');
                                        })
                                        ->leftJoinSub($users,'users',function($join){
                                            $join->on('users.id','loan_account_repayments.paid_by');
                                        })
                                        ->when($is_summarized,function($q, $data){
                                                if($data){
                                                    $q->groupBy('office_code','loan_code','payment_method_name');
                                                }
                                        });


        $deposit_to_loan_repayments = DB::table('deposit_to_loan_repayments')
                                        ->select(
                                           $dtlr_select
                                        )
                                        ->when($data['office_id'], function($q) use ($data){
                                            $office_ids = Office::lowerOffices($data['office_id'],true,true);
                                            $q->whereExists(function($q2) use ($office_ids){
                                                $client_ids = DB::table('clients')->select('client_id')->whereIn('office_id',$office_ids)->pluck('client_id')->toArray();
                                                $q2->from('loan_accounts')
                                                    ->whereIn('client_id',$client_ids)
                                                    ->whereColumn('deposit_to_loan_repayments.loan_account_id','loan_accounts.id');
                                            });
                                        })
                                        ->when($data['from_date'], function($q) use ($data){
                                            $q->when($data['to_date'],function($q2) use($data){
                                                $q2->whereBetween('deposit_to_loan_repayments.repayment_date',[$data['from_date'], $data['to_date']]);
                                            }); 
                                        })
                                        ->when($data['users'], function($q, $data){
                                            $q->whereIn('paid_by',$data);
                                        })
                                        ->leftJoinSub($loan_accounts,'loan_accounts',function($join){
                                            $join->on('loan_accounts.id','deposit_to_loan_repayments.loan_account_id');
                                        })
                                        ->leftJoinSub($loans,'loans',function($join){
                                            $join->on('loans.id','loan_accounts.loan_id');
                                        })
                                        ->leftJoinSub($clients,'clients',function($join){
                                            $join->on('clients.client_id','loan_accounts.client_id');
                                        })
                                        ->leftJoinSub($offices,'offices',function($join){
                                            $join->on('offices.id','clients.office_id');
                                        })
                                        ->leftJoinSub($payment_methods,'payment_methods',function($join){
                                            $join->on('payment_methods.id','deposit_to_loan_repayments.payment_method_id');
                                        })
                                        ->leftJoinSub($users,'users',function($join){
                                            $join->on('users.id','deposit_to_loan_repayments.paid_by');
                                        })
                                        ->leftJoinSub($deposit_accounts,'deposit_accounts',function($join){
                                            $join->on('deposit_accounts.id','deposit_to_loan_repayments.deposit_account_id');
                                        })
                                        ->leftJoinSub($deposits,'deposits',function($join){
                                            $join->on('deposits.id','deposit_accounts.deposit_id');
                                        })
                                        ->when($is_summarized,function($q, $data){
                                            if($data){
                                                $q->groupBy('office_code','loan_code','payment_method_name');
                                            }
                                        });

        
        $list = 124;
        
        

        if(is_null($data['type'])){
            $list = $deposit_to_loan_repayments
                    ->unionAll($loan_account_repayments);
        }else if($data['type']=='Loan Payment'){
            $list = $loan_account_repayments;
        }else if($data['type']=='CTLP'){
            $list = $deposit_to_loan_repayments;
        }
        

        if($is_summarized){
            $sum = clone $list;
            $sum = $sum->groupBy('office_code','loan_code')->get();
            $summary = [
                'number_of_accounts'=> $sum->sum('number_of_repayments'),
                'interest_paid'=>$sum->sum('interest_paid'),
                'principal_paid'=>$sum->sum('principal_paid'),
                'total_paid'=>$sum->sum('total_paid'),
            ];
        }else{
            $summary = [
                'number_of_accounts'=> $list->count(),
                'interest_paid'=>$list->sum('interest_paid'),
                'principal_paid'=>$list->sum('principal_paid'),
                'total_paid'=>$list->sum('total_paid'),
            ];
        }


     
        $data =  $paginated  ? $list->paginate($data['per_page']) : $list;
        return compact('data','summary','is_summarized');
    }
   
    public static function depositTransaction($data, $paginated = true){
        $space = " ";
        $type = collect($data['transaction_type']);
        $is_summarized = $data['is_summarized'];
        $users = DB::table('users');
        $deposits = DB::table('deposits');
        $clients = DB::table('clients');
        $payment_methods = DB::table('payment_methods');
        $deposit_accounts = DB::table('deposit_accounts');
        $offices = DB::table('offices');
        
        $transaction_types = DepositAccount::$deposit_transactions_report;

        $payments = \DB::table('deposit_payments')
                        ->select(
                            'deposit_payments.transaction_number as transaction_number',
                            'deposit_payments.deposit_account_id as deposit_account_id',
                            'deposit_payments.amount as amount',
                            'deposit_payments.balance as balance',
                            \DB::raw("IF(1=1,'Payment',NULL) as transaction_type"),
                            'payment_methods.name as payment_method_name',
                            'deposit_payments.repayment_date as repayment_date',
                            'offices.name as office_name',
                            'clients.client_id as client_id',
                            'deposits.product_id as deposit_type',
                            \DB::raw("CONCAT(users.firstname, '{$space}', users.lastname) as paid_by"),
                            \DB::raw("CONCAT(clients.firstname, '{$space}', clients.lastname) as client_name"),
                            'deposit_payments.notes as notes',
                            'paid_offices_at.name as paid_on',
                            'deposit_payments.created_at as timestamp'
                        )
                        ->when($data['office_id'],function($q,$data){
                            $q->whereExists(function($q2) use ($data){
                                $office_ids = Office::lowerOffices($data,true,true);
                                $client_ids = DB::table('clients')->whereIn('office_id',$office_ids)->select('client_id')->get();
                                
                                $q2->from('deposit_accounts')
                                    ->whereIn('client_id',$client_ids)
                                    ->whereColumn('deposit_accounts.client_id','deposit_payments.deposit_account_id');
                            });
                        })
                        ->when($data['from_date'], function($q) use ($data){
                            $q->when($data['to_date'],function($q2) use($data){
                                $q2->whereBetweenDate('deposit_payments.repayment_date',[$data['from_date'], $data['to_date']]);
                            }); 
                        })
                        ->when($data['amount_from'], function($q) use ($data){
                            $q->when($data['amount_to'],function($q2) use($data){
                                $q2->whereBetween('deposit_payments.amount',[$data['amount_from'], $data['amount_to']]);
                            }); 
                        })
                        ->when($data['transaction_by'], function($q,$data){
                            $q->whereIn('paid_by',$data);
                        })
                        ->where('deposit_payments.reverted',false)
                        ->leftJoinSub($deposit_accounts,'deposit_accounts',function($join){
                            $join->on('deposit_accounts.id','deposit_payments.deposit_account_id');
                        })
                        
                        ->leftJoinSub($deposits,'deposits',function($join){
                            $join->on('deposits.id','deposit_accounts.deposit_id');
                        })
                        
                        ->leftJoinSub($payment_methods,'payment_methods',function($join){
                            $join->on('payment_methods.id','deposit_payments.payment_method_id');
                        })
                        ->leftJoinSub($offices,'paid_offices_at',function($join){
                            $join->on('paid_offices_at.id','deposit_payments.office_id');
                        })
                        ->leftJoinSub($users,'users',function($join){
                            $join->on('users.id','deposit_payments.paid_by');
                        })
                        ->leftJoinSub($clients,'clients',function($join){
                            $join->on('clients.client_id','deposit_accounts.client_id');
                        })
                        ->leftJoinSub($offices,'offices',function($join){
                            $join->on('offices.id','deposit_payments.office_id');
                        });

        $withdrawals = \DB::table('deposit_withdrawals')
                        ->select(
                            'deposit_withdrawals.transaction_number as transaction_number',
                            'deposit_withdrawals.deposit_account_id as deposit_account_id',
                            'deposit_withdrawals.amount as amount',
                            'deposit_withdrawals.balance as balance',
                            \DB::raw("IF(1=1,'Withdrawal',NULL) as transaction_type"),
                            'payment_methods.name as payment_method_name',
                            'deposit_withdrawals.repayment_date as repaymaent_date',
                            'offices.name as office_name',
                            'clients.client_id as client_id',
                            'deposits.product_id as deposit_type',
                            \DB::raw("CONCAT(users.firstname, '{$space}', users.lastname) as paid_by"),
                            \DB::raw("CONCAT(clients.firstname, '{$space}', clients.lastname) as client_name"),
                            'deposit_withdrawals.notes as notes',
                            'paid_offices_at.name as paid_on',
                            'deposit_withdrawals.created_at as timestamp'
                        )
                        ->when($data['office_id'],function($q,$data){
                            $q->whereExists(function($q2) use ($data){
                                $office_ids = Office::lowerOffices($data,true,true);
                                $client_ids = DB::table('clients')->whereIn('office_id',$office_ids)->select('client_id')->get();
                                
                                $q2->from('deposit_accounts')
                                    ->whereIn('client_id',$client_ids)
                                    ->whereColumn('deposit_accounts.client_id','deposit_withdrawals.deposit_account_id');
                            });
                        })
                        ->when($data['from_date'], function($q) use ($data){
                            $q->when($data['to_date'],function($q2) use($data){
                                $q2->whereBetweenDate('deposit_withdrawals.repayment_date',[$data['from_date'], $data['to_date']]);
                            }); 
                        })
                        ->when($data['amount_from'], function($q) use ($data){
                            $q->when($data['amount_to'],function($q2) use($data){
                                $q2->whereBetween('deposit_withdrawals.amount',[$data['amount_from'], $data['amount_to']]);
                            }); 
                        })
                        ->when($data['transaction_by'], function($q,$data){
                            $q->whereIn('paid_by',$data);
                        })
                        ->where('deposit_withdrawals.reverted',false)
                        ->leftJoinSub($deposit_accounts,'deposit_accounts',function($join){
                            $join->on('deposit_accounts.id','deposit_withdrawals.deposit_account_id');
                        })
                        ->leftJoinSub($deposits,'deposits',function($join){
                            $join->on('deposits.id','deposit_accounts.deposit_id');
                        })
                        ->leftJoinSub($payment_methods,'payment_methods',function($join){
                            $join->on('payment_methods.id','deposit_withdrawals.payment_method_id');
                        })
                        ->leftJoinSub($offices,'paid_offices_at',function($join){
                            $join->on('paid_offices_at.id','deposit_withdrawals.office_id');
                        })
                        ->leftJoinSub($users,'users',function($join){
                            $join->on('users.id','deposit_withdrawals.paid_by');
                        })
                        ->leftJoinSub($clients,'clients',function($join){
                            $join->on('clients.client_id','deposit_accounts.client_id');
                        })
                        ->leftJoinSub($offices,'offices',function($join){
                            $join->on('offices.id','deposit_withdrawals.office_id');
                        });
        $ctlp_withdrawals = \DB::table('deposit_withdrawals')
                        ->select(
                            'deposit_withdrawals.transaction_number as transaction_number',
                            'deposit_withdrawals.deposit_account_id as deposit_account_id',
                            'deposit_withdrawals.amount as amount',
                            'deposit_withdrawals.balance as balance',
                            \DB::raw("IF(1=1,'Withdrawal',NULL) as transaction_type"),
                            'payment_methods.name as payment_method_name',
                            'deposit_withdrawals.repayment_date as repaymaent_date',
                            'offices.name as office_name',
                            'clients.client_id as client_id',
                            'deposits.product_id as deposit_type',
                            \DB::raw("CONCAT(users.firstname, '{$space}', users.lastname) as paid_by"),
                            \DB::raw("CONCAT(clients.firstname, '{$space}', clients.lastname) as client_name"),
                            'deposit_withdrawals.notes as notes',
                            'paid_offices_at.name as paid_on',
                            'deposit_withdrawals.created_at as timestamp'
                        )
                        ->when($data['office_id'],function($q,$data){
                            $q->whereExists(function($q2) use ($data){
                                $office_ids = Office::lowerOffices($data,true,true);
                                $client_ids = DB::table('clients')->whereIn('office_id',$office_ids)->select('client_id')->get();
                                
                                $q2->from('deposit_accounts')
                                    ->whereIn('client_id',$client_ids)
                                    ->whereColumn('deposit_accounts.client_id','deposit_withdrawals.deposit_account_id');
                            });
                        })
                        ->when($data['from_date'], function($q) use ($data){
                            $q->when($data['to_date'],function($q2) use($data){
                                $q2->whereBetweenDate('deposit_withdrawals.repayment_date',[$data['from_date'], $data['to_date']]);
                            }); 
                        })
                        ->when($data['amount_from'], function($q) use ($data){
                            $q->when($data['amount_to'],function($q2) use($data){
                                $q2->whereBetween('deposit_withdrawals.amount',[$data['amount_from'], $data['amount_to']]);
                            }); 
                        })
                        ->where('deposit_withdrawals.transaction_number','LIKE','X%')
                        ->where('deposit_withdrawals.reverted',false)

                        ->when($data['transaction_by'], function($q,$data){
                            $q->whereIn('paid_by',$data);
                        })
                        ->leftJoinSub($deposit_accounts,'deposit_accounts',function($join){
                            $join->on('deposit_accounts.id','deposit_withdrawals.deposit_account_id');
                        })
                        ->leftJoinSub($deposits,'deposits',function($join){
                            $join->on('deposits.id','deposit_accounts.deposit_id');
                        })
                        ->leftJoinSub($payment_methods,'payment_methods',function($join){
                            $join->on('payment_methods.id','deposit_withdrawals.payment_method_id');
                        })
                        ->leftJoinSub($offices,'paid_offices_at',function($join){
                            $join->on('paid_offices_at.id','deposit_withdrawals.office_id');
                        })
                        ->leftJoinSub($users,'users',function($join){
                            $join->on('users.id','deposit_withdrawals.paid_by');
                        })
                        ->leftJoinSub($clients,'clients',function($join){
                            $join->on('clients.client_id','deposit_accounts.client_id');
                        })
                        ->leftJoinSub($offices,'offices',function($join){
                            $join->on('offices.id','deposit_withdrawals.office_id');
                        });

        $interest_posting = \DB::table('deposit_interest_posts')
                        ->select(
                            'deposit_interest_posts.transaction_number as transaction_number',
                            'deposit_interest_posts.deposit_account_id as deposit_account_id',
                            'deposit_interest_posts.amount as amount',
                            'deposit_interest_posts.balance as balance',
                            \DB::raw("IF(1=1,'Interest Posting',NULL) as transaction_type"),
                            'payment_methods.name as payment_method_name',
                            'deposit_interest_posts.repayment_date as repayment_date',
                            'offices.name as office_name',
                            'clients.client_id as client_id',
                            'deposits.product_id as deposit_type',
                            \DB::raw("CONCAT(users.firstname, '{$space}', users.lastname) as paid_by"),
                            \DB::raw("CONCAT(clients.firstname, '{$space}', clients.lastname) as client_name"),
                            'deposit_interest_posts.notes as notes',
                            'paid_offices_at.name as paid_on',
                            'deposit_interest_posts.created_at as timestamp'
                        )
                        ->when($data['office_id'],function($q,$data){
                            $q->whereExists(function($q2) use ($data){
                                $office_ids = Office::lowerOffices($data,true,true);
                                $client_ids = DB::table('clients')->whereIn('office_id',$office_ids)->select('client_id')->get();
                                
                                $q2->from('deposit_accounts')
                                    ->whereIn('client_id',$client_ids)
                                    ->whereColumn('deposit_accounts.client_id','deposit_interest_posts.deposit_account_id');
                            });
                        })
                        ->when($data['from_date'], function($q) use ($data){
                            $q->when($data['to_date'],function($q2) use($data){
                                $q2->whereBetweenDate('deposit_interest_posts.repayment_date',[$data['from_date'], $data['to_date']]);
                            }); 
                        })
                        ->when($data['amount_from'], function($q) use ($data){
                            $q->when($data['amount_to'],function($q2) use($data){
                                $q2->whereBetween('deposit_interest_posts.amount',[$data['amount_from'], $data['amount_to']]);
                            }); 
                        })
                        ->when($data['transaction_by'], function($q,$data){
                            $q->whereIn('paid_by',$data);
                        })
                        ->leftJoinSub($deposit_accounts,'deposit_accounts',function($join){
                            $join->on('deposit_accounts.id','deposit_interest_posts.deposit_account_id');
                        })
                        ->leftJoinSub($deposits,'deposits',function($join){
                            $join->on('deposits.id','deposit_accounts.deposit_id');
                        })
                        ->leftJoinSub($payment_methods,'payment_methods',function($join){
                            $join->on('payment_methods.id','deposit_interest_posts.payment_method_id');
                        })
                        ->leftJoinSub($offices,'paid_offices_at',function($join){
                            $join->on('paid_offices_at.id','deposit_interest_posts.office_id');
                        })
                        ->leftJoinSub($users,'users',function($join){
                            $join->on('users.id','deposit_interest_posts.paid_by');
                        })
                        ->leftJoinSub($clients,'clients',function($join){
                            $join->on('clients.client_id','deposit_accounts.client_id');
                        })
                        ->leftJoinSub($offices,'offices',function($join){
                            $join->on('offices.id','deposit_interest_posts.office_id');
                        });
        
        
        $x;

        $tables = [];
        if(count($type) == 0){
            $list = $payments
                        ->unionAll($withdrawals)
                        ->unionAll($interest_posting)
                        ->unionAll($ctlp_withdrawals)
                        ->orderBy('timestamp','desc');
        }else{
            if(in_array('Payment',$type)){
                $tables[] = $payments;
            }
            if(in_array('Withdrawal',$type)){
                $tables[] = $withdrawals;
            }
            if(in_array('Interest Posting',$type)){
                $tables[] = $interest_posting;
            }
            if(in_array('CTLP Withdrawal',$type)){
                $tables[] = $ctlp_withdrawals;
            }
            

            if(count($tables) == 1){
                $list = $tables[0];
            }else{
                $list = $tables[0]
                        ->when($tables, function($q,$data){
                            $ctr = 0; //index, primary table
                            foreach($data as $table){
                                if($ctr > 0){
                                    $q->unionAll($table);
                                }
                                $ctr++;
                            }
                        });
            }
        }
        $summary = clone $list;
        // $summary = $summary->select(
        //         DB::raw('count(transactions.id) as number_of_transactions'),
        //         DB::raw('sum(morphed_table.amount) as total_amount'),
        //         DB::raw('SUM(morphed_table.balance) as balance'),
        // )->first();
     
 
        $data = $paginated ? $list->paginate($data['per_page']) : $list->select($select);
        return compact('data','summary','is_summarized');
    }
}
