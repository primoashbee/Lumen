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
}
