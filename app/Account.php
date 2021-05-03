<?php

namespace App;

use App\Loan;
use App\Deposit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class Account extends Model
{
    protected $fillable = ['client_id','accountable_id','accountable_id_type'];
    public function accountable(){
        return $this->morphTo();
    }
    public function client(){
        return $this->belongsTo(Client::class,'client_id','client_id');
    }
    public static function repaymentsFromDate(array $array, $create_ccr = false){
        $office_id = $array['office_id'];

        $date = Carbon::parse($array['date']);
        $loan_product_id = $array['loan_account_id'];

        $office = Office::find($office_id);
        $ids = $office->getLowerOfficeIDS();

        $client_ids = Client::select('client_id')
            ->whereIn('office_id',$ids)
            ->pluck('client_id');

        $deposit_ids = $array['deposit_product_ids'];
        
 
        $accounts = LoanAccount::with([
            'client'=>function($q) use($client_ids){
                $q->select('client_id','firstname','lastname');
                $q->whereIn('client_id',$client_ids);
            },
            'product:id,code,installment_method',
            'client.deposits'=>function($q) use ($deposit_ids){
                $q->select('id','client_id','deposit_id','balance');
                $q->whereIn('deposit_id',$deposit_ids);
                $q->orderBy('deposit_id','ASC');
            },
            'client.deposits.type' => function($q) {
                $q->select('id','product_id','minimum_deposit_per_transaction');
            }
        ])
        ->select('id','client_id','loan_id','number_of_months','number_of_installments','total_balance')
        ->where('loan_id',$loan_product_id)
        ->whereNotNull('approved_at')
        ->whereNotNull('disbursed_at')
        ->whereNull('closed_by')
        ->whereIn('client_id',$client_ids)
        ->get();

        $deposit_types = Deposit::select('product_id')->whereIn('id',$deposit_ids)->orderBy('id','ASC')->pluck('product_id');
        $deposit_summary = [];
        $deposit_types->map(function($x) use(&$deposit_summary){
            
            $deposit_summary[] = ['type'=>$x,'total_balance'=>0];
        });
        
        $list = collect();
        $total_interest = 0;
        $total_principal = 0;
        $total_amount_due = 0;

        $total = ['loan'=>[
                    'loan_balance' =>0,
                    '_loan_balance'=> null,
                    'overdue'=>[
                        'total_interest'=>0,
                        'total_principal'=>0,
                        'total_amount_due'=>0,
                    ],
                    'due'=>[
                        'total_interest'=>0,
                        'total_principal'=>0,
                        'total_amount_due'=>0,
                    ],
                    'total_due'=>[
                        'total_interest'=>0,
                        'total_principal'=>0,
                        'total_amount_due'=>0,
                    ]
                ],'deposits'=>$deposit_summary ];

        $overdue = null;
        if ($accounts->count() > 0) {
            $accounts->map(function ($account) use ($date, &$list, &$total, &$overdue) {
                $repayment_info =$account->getDuesFromDate($date);
                
                $overdue = $repayment_info->overdue;
                $due = $repayment_info->due;
                $total_due = $repayment_info->total_due;

                $account['overdue'] = $overdue;
                $account['due'] = $due;
                $account['total_due'] = $total_due;
                $account['_total_balance'] = money($account->getRawOriginal('total_balance'),2);
                $account['total_due'];
                
                
                $total['loan']['loan_balance'] += $account->getRawOriginal('total_balance');
                $total['loan']['overdue']['total_interest']+=$overdue->interest;
                $total['loan']['overdue']['total_principal']+=$overdue->principal;
                $total['loan']['overdue']['total_amount_due']+=$overdue->total;
                
                
                $total['loan']['due']['total_interest']+=$due->interest;
                $total['loan']['due']['total_principal']+=$due->principal;
                $total['loan']['due']['total_amount_due']+=$due->total;

                $total['loan']['total_due']['total_interest']+=$total_due->interest;
                $total['loan']['total_due']['total_principal']+=$total_due->principal;
                $total['loan']['total_due']['total_amount_due']+=$total_due->total;
                
                $account->client->deposits->map(function($dep) use (&$total){
                    collect($total['deposits'])->each(function($item, $key) use ($dep, &$total){
                        if($dep->type->product_id==$item['type']){
                            $total['deposits'][$key]['total_balance'] += $dep->balance;
                            $total['deposits'][$key]['_total_balance'] = money($total['deposits'][$key]['total_balance'],2);
                        }
                    });
                });
                $list->push($account);
            });
        }
           
        $total['loan']['overdue']['_total_interest']=money($total['loan']['overdue']['total_interest'],2);
        $total['loan']['overdue']['_total_principal']=money($total['loan']['overdue']['total_principal'],2);
        $total['loan']['overdue']['_total_amount_due']=money($total['loan']['overdue']['total_amount_due'],2);
        
        $total['loan']['due']['_total_interest']  = money($total['loan']['due']['total_interest'],2);
        $total['loan']['due']['_total_principal'] = money($total['loan']['due']['total_principal'],2);
        $total['loan']['due']['_total_amount_due']= money($total['loan']['due']['total_amount_due'],2);

        $total['loan']['total_due']['_total_interest']  = money($total['loan']['total_due']['total_interest'],2);
        $total['loan']['total_due']['_total_principal'] = money($total['loan']['total_due']['total_principal'],2);
        $total['loan']['total_due']['_total_amount_due']= money($total['loan']['total_due']['total_amount_due'],2);
        

        $total['loan']['_loan_balance'] = money($total['loan']['loan_balance'],2);
        $summary = new \stdClass;
        $summary->loan_accounts = $list;
        // $summary->interest_total = $total_interest;
        // $summary->total_principal = $total_principal;
        // $summary->total_amount = $total_amount_due;

        $summary->has_loan = count($list) > 0;
        $summary->has_deposit = count($deposit_ids) > 0;
        $summary->loan_type = Loan::select('code')->find(1)->code;
        $summary->deposit_types = $deposit_types;
        $summary->total = $total;
        $summary->repayment_date = $date->format('F d, Y');
        $summary->office = $office->name;

        $file = public_path('temp/').$summary->office.' - '.$summary->repayment_date.'.pdf';
        $name = $summary->office.' - '.$summary->repayment_date.'.pdf';
        $summary->name = $name;
        
        return $summary;
    }

    public static function ccrFromDate(array $array){
        $loan_product_id = $array['loan_product_id'];
        $office = Office::find($array['office_id']);
        $ids = $office->getLowerOfficeIDS();
    
        $client_ids = Client::select('client_id')
            ->whereIn('office_id',$ids)
            ->pluck('client_id');
    
        // $deposit_ids = [1,2];
        $deposit_ids = $array['deposit_product_ids'];
        $date = Carbon::parse($array['date'])->startOfDay();
    
        $has_deposit = count($deposit_ids) > 0;
        $clients = \DB::table('clients');
        
        
 
        //if date is future, interest due will be interest
        if($date > now()->startOfDay()){
            $installment_due = \DB::table('loan_account_installments')
                ->select(\DB::raw("loan_account_id, sum(principal_due + interest) as installment_due, sum(interest) as interest_due, sum(principal_due) as principal_due"))
                ->where('date', $date)
                ->groupBy('loan_account_id');
                // ->get();

                $total_due  =  \DB::table('loan_account_installments')
                ->select('loan_account_id',
                    \DB::raw('
                        round(sum(principal+interest),2) as due,  
                        round(sum(principal_due),2) as principal_due, 
                        round(sum(interest),2) as interest_due
                        ')
                    )
                ->groupBy('loan_account_id')
                ->where('date','<=',$date);

                $overdues  =  \DB::table('loan_account_installments')
                ->select('loan_account_id',
                    \DB::raw('
                        round(sum(principal_due+interest),2) as overdue, 
                        round(sum(principal_due),2) as principal_overdue, 
                        round(sum(interest),2) as interest_overdue
                        ')
                    )
                ->groupBy('loan_account_id')
                ->where('date','<',$date);
        }else{
            $installment_due = \DB::table('loan_account_installments')
                ->select(\DB::raw("loan_account_id, sum(amount_due) as installment_due, sum(interest_due) as interest_due, sum(principal_due) as principal_due"))
                ->where('date', $date)
                ->groupBy('loan_account_id');

            $total_due  =  \DB::table('loan_account_installments')
                        ->select('loan_account_id',
                            \DB::raw('
                                round(sum(amount_due),2) as due,  
                                round(sum(principal_due),2) as principal_due, 
                                round(sum(interest_due),2) as interest_due
                                ')
                            )
                        ->groupBy('loan_account_id')
                        ->where('date','<=',$date);
            
            $overdues  =  \DB::table('loan_account_installments')
            ->select('loan_account_id',
                \DB::raw('
                    round(sum(amount_due),2) as overdue, 
                    round(sum(principal_due),2) as principal_overdue, 
                    round(sum(interest_due),2) as interest_overdue
                    ')
                )
            ->groupBy('loan_account_id')
            ->where('date','<',$date);
        }
        $deposit_select = '';
        $select = [
            \DB::raw('loan_type.code as loan_code'),
            'la.id as loan_account_id','la.loan_id','la.client_id','la.number_of_months','la.number_of_installments','la.total_balance',
            'c.firstname','c.lastname','c.office_id', \DB::raw("concat(c.firstname,' ',c.lastname) as fullname"),
            \DB::raw('IFNULL(dues.due,0) as due_due'),\DB::raw('IFNULL(dues.interest_due,0) as due_interest'),\DB::raw('IFNULL(dues.principal_due,0) as due_principal'),
            \DB::raw('IFNULL(overdues.overdue,0) as overdue_due'),\DB::raw('IFNULL(overdues.interest_overdue,0) as overdue_interest'),\DB::raw('IFNULL(overdues.principal_overdue,0) as overdue_principal'),
            \DB::raw('IFNULL(installment_due.installment_due,0) as installment_due'), \DB::raw('IFNULL(installment_due.interest_due,0) as installment_interest_due'),\DB::raw('IFNULL(installment_due.principal_due,0) as installment_principal_due'),
        ];
    
        if($has_deposit){
            
                $ctr = 0;
                $list = collect(Deposit::whereIn('id',$deposit_ids)->get());
                foreach ($deposit_ids as $id) {
                    $table_alias = "deposit_account_".$ctr;
                    $named_balance = $table_alias.'.balance as '.$list->where('id',$id)->first()->product_id; 
                    $select[] = \DB::raw($named_balance);
                    $ctr++;
                }
        }
        $loan_type = \DB::table('loans');
        
        $accounts = \DB::table('loan_accounts as la')
            ->select(
                $select
            )
            ->where('la.loan_id',$loan_product_id)
            ->whereNotNull('la.approved_at')
            ->whereNotNull('la.disbursed_at')
            ->whereNull('la.closed_by')
            ->whereIn('la.client_id',$client_ids)
            ->leftJoinSub($loan_type,'loan_type',function($join){
                $join->on('loan_type.id','=','la.loan_id');
            })
            ->leftJoinSub($clients,'c',function($join){
                $join->on('la.client_id','=','c.client_id');
            })
            ->when($total_due,function($q,$total_due){                    
                $q->leftJoinSub($total_due,'dues',function($join){
                    $join->on('dues.loan_account_id','=','la.id');
                });
            })
            ->when($overdues,function($q,$overdues){
                $q->leftJoinSub($overdues,'overdues',function($join){
                    $join->on('overdues.loan_account_id','=','la.id');
                });
            })
    
            ->when($installment_due,function($q,$installment_due){
                $q->leftJoinSub($installment_due, 'installment_due', function ($join) {
                    $join->on('installment_due.loan_account_id', '=', 'la.id');
                });
              
            })
            ->when($deposit_ids,function($q,$deposit_ids) use ($client_ids){
                $deps = [];
                //separated table per account
                foreach($deposit_ids as $d_id){
                    $deps[] = \DB::table('deposit_accounts')
                                ->where('deposit_id',$d_id)
                                ->whereIn('client_id',$client_ids);
                                // ->get();
                }
    
                foreach($deps as $key=>$value){
                    $alias = 'deposit_account_'.$key;
                    $q->leftJoinSub($value, $alias, function($join) use($alias){
                        $join->on($alias.'.client_id','=','c.client_id');
                    });
                }
          
            })
            ->get();
    
        return ($accounts);
    }
}
