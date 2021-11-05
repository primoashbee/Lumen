<?php

namespace App;

use App\User;
use App\Deposit;
use Carbon\Carbon;
use App\DepositPayment;
use App\Rules\OfficeID;
use App\DepositWithdrawal;
use App\DepositTransaction;
use App\PostedAccruedInterest;
use App\Rules\TransactionType;
use App\Rules\PaymentMethodList;
use App\Rules\PreventFutureDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use App\Rules\WithdrawAmountLessThanBalance;
use App\Rules\PreventLaterThanLastTransactionDate;
use Exception;

class DepositAccount extends Model
{
    public static $deposit_transactions_report = ["Payment","Withdrawal","CTLP Withdrawal","Interest Posting"];
    // protected $appends = [
    //     'balance_formatted',
    //     'new_balance',
    //     'new_balance_formatted',
    //     'accrued_interest_formatted',
    // ];
    protected $fillable = [
        'client_id',
        'deposit_id',
        'balance',
        'accrued_interest',
        'status',
        'closed_by',
        'closed_at'
    ];
    protected $casts = [
        'created_at' => 'datetime:F d, Y',
        'closed_at' => 'datetime:F d, Y',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'closed_at'

    ];
    public function type(){
        return $this->belongsTo(Deposit::class,'deposit_id');
    }

    public function getBalanceFormattedAttribute(){
        // return env('CURRENCY_SIGN').' '.number_format($value,2,'.',',');
        return env('CURRENCY_SIGN').' '.numberFormat($this->getRawOriginal('balance'));
        // }
    }
    

    public function deposit(array $data, $single_payment = false, $for_revertion=false,$from_ctlp=false){

            $transaction_number = uniqid();
            $new_balance = $this->getRawOriginal('balance') + $data['amount'];
            $transaction_number = 'D'.str_replace('.','',microtime(true));
            //Create Payment
            $payment = $this->payments()->create([
                'transaction_number'=>$transaction_number,
                'amount'=>$data['amount'],
                'balance'=>$new_balance,
                'payment_method_id'=>$data['payment_method_id'],
                'repayment_date'=>$data['repayment_date'],
                'office_id'=>$data['office_id'],
                'paid_by'=>$data['paid_by'],
                'notes'=>$data['notes'],
                'reverted'=>$for_revertion,
                'revertion'=>$for_revertion
            ]);
            
            $this->balance = $new_balance;
            
            
            return $this->save();
            
    }

    public function withdraw(array $data, $single_withdraw =false, $for_revertion = false){
        if (!$for_revertion) {
            if ($this->getRawOriginal('balance') < $data['amount']) {
                return false;
            }
        }
        $new_balance = $this->getRawOriginal('balance') - $data['amount'];

    
        $transaction_number = 'W'.str_replace('.','',microtime(true));
        $new_balance = $this->getRawOriginal('balance') - $data['amount'];
        
        //Create Payment
        $withdrawal = $this->withdrawals()->create([
            'transaction_number'=>$transaction_number,
            'amount'=>$data['amount'],
            'balance'=>$new_balance,
            'payment_method_id'=>$data['payment_method_id'],
            'repayment_date'=>$data['repayment_date'],
            'office_id'=>$data['office_id'],
            'paid_by'=>$data['paid_by'],
            'notes'=>$data['notes'],
            'revertion'=>$for_revertion,
            'reverted'=>$for_revertion

        ]);

        $this->balance = $new_balance;
        $this->save();
            
    }

    public function payCTLP(array $data, $single_payment=false){
  
        if ($this->getRawOriginal('balance') < $data['amount']) {
            return false;
        }
        $new_balance = $this->getRawOriginal('balance') - $data['amount'];

      
        // $transaction_number = 'X'.str_replace('.','',microtime(true));
        
        $withdrawal = $this->withdrawals()->create([
            'transaction_number'=>$data['transaction_number'],
            'amount'=>$data['amount'],
            'balance'=>$new_balance,
            'payment_method_id'=>$data['payment_method_id'],
            'repayment_date'=>$data['repayment_date'],
            'office_id'=>$data['office_id'],
            'paid_by'=>$data['paid_by'],
            'notes'=>$data['notes'],
        ]);

        $this->balance = $new_balance;
        $this->save();

        $payload = ['date'=>$data['repayment_date'],'amount'=>$data['amount']];
        if ($single_payment) {
            event(new \App\Events\DepositTransaction($payload, $data['office_id'], $data['user_id'], $data['payment_method_id'], 'CTLP'));
        }
        return $withdrawal;

    }
    public function revertPayCTLP(array $data){
        
        $new_balance = $this->getRawOriginal('balance') + $data['amount'];
        DepositTransaction::create([
            'transaction_id' => uniqid(),
            'deposit_account_id' => $this->id,
            'transaction_type'=>'Deposit',
            'amount'=>$data['amount'],
            'payment_method_id'=>$data['payment_method_id'],
            'repayment_date'=>$data['repayment_date'],
            'user_id'=> $data['user_id'],
            'balance' => $new_balance
        ]);
            
        
        $this->balance = $new_balance;
        return $this->save();
    }


    
    public function transactions($successful_only=false){
        $space = ' ';
        $deposit_accounts = \DB::table('deposit_accounts');
        $payment_methods = \DB::table('payment_methods');
        $users = \DB::table('users');
        $offices = \DB::table('offices');

        $id = $this->id;
        
        $deposit_withdrawals = \DB::table('deposit_withdrawals')
                                ->select(
                                    'deposit_withdrawals.transaction_number',
                                    'deposit_withdrawals.amount',
                                    'deposit_withdrawals.balance',
                                    'payment_methods.name as payment_method_name',
                                    'deposit_withdrawals.repayment_date',
                                    'deposit_withdrawals.created_at as transaction_date',
                                    'deposit_withdrawals.reverted',
                                    'deposit_withdrawals.reverted_by',
                                    'deposit_withdrawals.revertion',
                                    \DB::raw("IF(1=1,'Withdrawal',NULL) as type"),
                                    \DB::raw("CONCAT(users.firstname, '{$space}', users.lastname) as paid_by"),
                                )
                                ->when($successful_only, function($q,$data){
                                    if($data){
                                        $q->where('reverted',false);
                                    }
                                    
                                })
                                ->leftJoinSub($payment_methods,'payment_methods',function($join){
                                    $join->on('payment_methods.id','deposit_withdrawals.payment_method_id');
                                })
                                ->leftJoinSub($users,'users',function($join){
                                    $join->on('users.id','deposit_withdrawals.paid_by');
                                })

                                ->where('deposit_withdrawals.deposit_account_id',$id);
        $deposit_interest_posts = \DB::table('deposit_interest_posts')
                                ->select(
                                    'deposit_interest_posts.transaction_number',
                                    'deposit_interest_posts.amount',
                                    'deposit_interest_posts.balance',
                                    'payment_methods.name as payment_method_name',
                                    'deposit_interest_posts.repayment_date',
                                    'deposit_interest_posts.created_at as transaction_date',
                                    'deposit_interest_posts.reverted',
                                    'deposit_interest_posts.reverted_by',
                                    'deposit_interest_posts.revertion',
                                    \DB::raw("IF(1=1,'Interest Posting',NULL) as type"),
                                    \DB::raw("CONCAT(users.firstname, '{$space}', users.lastname) as paid_by"),
                                )
                                ->when($successful_only, function($q,$data){
                                    if($data){
                                        $q->where('reverted',false);
                                    }
                                    
                                })
                                ->leftJoinSub($payment_methods,'payment_methods',function($join){
                                    $join->on('payment_methods.id','deposit_interest_posts.payment_method_id');
                                })
                                ->leftJoinSub($users,'users',function($join){
                                    $join->on('users.id','deposit_interest_posts.paid_by');
                                })

                                ->where('deposit_interest_posts.deposit_account_id',$id);

        $list = \DB::table('deposit_payments')
                                ->select(
                                    'deposit_payments.transaction_number',
                                    'deposit_payments.amount',
                                    'deposit_payments.balance',
                                    'payment_methods.name as payment_method_name',
                                    'deposit_payments.repayment_date',
                                    'deposit_payments.created_at as transaction_date',
                                    'deposit_payments.reverted',
                                    'deposit_payments.reverted_by',
                                    'deposit_payments.revertion',
                                    \DB::raw("IF(1=1,'Payment',NULL) as type"),
                                    \DB::raw("CONCAT(users.firstname, '{$space}', users.lastname) as paid_by"),
                                )
                                ->leftJoinSub($payment_methods,'payment_methods',function($join){
                                    $join->on('payment_methods.id','deposit_payments.payment_method_id');
                                })
                                ->leftJoinSub($users,'users',function($join){
                                    $join->on('users.id','deposit_payments.paid_by');
                                })
                                ->when($successful_only, function($q,$data){
                                    if($data){
                                        $q->where('reverted',false);
                                    }
                                    
                                })
                                ->where('deposit_payments.deposit_account_id',$id)
                                ->unionAll($deposit_withdrawals)
                                ->unionAll($deposit_interest_posts)

                                ->orderBy('transaction_date','desc');
        return $list;
    }

    public function withdrawals(){
        return $this->hasMany(DepositWithdrawal::class);
        // return $this->morphMany(DepositWithdrawal::class,'withdrawalable');
    }
    public function payments(){
        return $this->hasMany(DepositPayment::class);
    }
    public function interestPostings(){
        return $this->hasMany(DepositInterestPost::class);
    }
    public function client(){
        return $this->belongsTo(Client::class,'client_id','client_id');
    }

    public function hasAccruedInterestForToday(){
       $post = DailyAccruedInterest::where('deposit_account_id',$this->id)->orderBy('created_at')->first();
       if($post !== null){           
            $days = $post->created_at->diffInDays(Carbon::now());
            return $days == 0 ? false : true;
       }
       return false;
    }


    public static function listForAccruingInterestToday(){
        $latestPostings = DB::table('daily_accrued_interests')
                                ->select('id', 'deposit_account_id', 'amount')
                                ->where(DB::raw('date(created_at)'),'=',DB::raw('CURDATE()'));
                                
        $depositAccounts = DB::table('deposit_accounts as da')
                                ->select('da.id as da_id', 'da.client_id', 'pai.amount')
                                ->leftJoinSub($latestPostings,'pai', function($join){
                                    $join->on('da.id','=','pai.deposit_account_id');
                                })->whereNull('pai.id');
        return DepositAccount::find($depositAccounts->get()->pluck('da_id'));

    }

    public static function accrueInterestAll(){
        $list = DepositAccount::listForAccruingInterestToday();

        $posting_records = array();
        $ctr=0;
        if($list->count() > 0){
            $list->map(function($item) use (&$posting_records, &$ctr){
                $posting_records[] = $item->accrueInterest();
                $ctr++;
            });
        }
        
        return DailyAccruedInterest::insert($posting_records);
        
        
    }

    public function accrueInterest($by_scheduler=true){
                $info = array();
                $info['user_id'] = 1;
                if(!$by_scheduler){
                    $info['user_id'] = auth()->user()->id;
                }
                $interest_rate = $this->type->getRawOriginal('interest_rate') / 100;
                $daily_interest_rate = $interest_rate / 365;
                $accrued_interest_today = round($daily_interest_rate * $this->getRawOriginal('balance'),2);
                $accrued_interest = $this->getRawOriginal('accrued_interest');
                $accumulated_accrued_interest =  $accrued_interest + $accrued_interest_today;
                $this->accrued_interest = $accumulated_accrued_interest;

                $info['deposit_account_id'] = $this->id;
                $info['amount'] = $accrued_interest_today; 
                $info['created_at'] = Carbon::now(); 
                $info['updated_at'] = Carbon::now(); 
                
                $this->save();
                return $info;
   
    }


    public static function listForInterestPosting($office_id=null){
        if($office_id==null){
            return DepositAccount::where('accrued_interest','>',0)->get();
        }
        $ids =  Client::where('office_id',$office_id)->get();

        return DepositAccount::whereIn('client_id',$ids)->where(function($query){
            $query->where('accrued_interest','>',0);
        })->get();


    }
    public function postInterest(array $data, $single_posting=false){   
        $current_balance = $this->getRawOriginal('balance');
        $accrued_interest = $this->getRawOriginal('accrued_interest');
        $new_balance = $current_balance + $accrued_interest;
        $this->accrued_interest = 0;
        $this->balance = $new_balance;

        $transaction_number = 'P'.str_replace('.','',microtime(true));
        $data['payment_method_id'] = PaymentMethod::interestPosting()->id;
        if ($accrued_interest > 0) {
            $posting = $this->interestPostings()->create([
                    'amount'=>$accrued_interest,
                    'transaction_number'=>$transaction_number,
                    'payment_method_id'=>$data['payment_method_id'],
                    'repayment_date'=>Carbon::now(),
                    'office_id'=>$data['office_id'],
                    'paid_by'=> $data['user_id'],
                    'balance' => $new_balance
                ]);

            $posting->jv()->create([
                    'journal_voucher_number'=>$data['journal_voucher_number'],
                    'transaction_date'=>now(),
                    'office_id'=>$data['office_id'],
                    'notes'=>'Interest Posting for Deposit Account'
                ]);

            if ($single_posting) {
                $repayment = now()->toDateString();
                $payload = ['date'=>$repayment,'amount'=>$accrued_interest];
                event(new \App\Events\DepositTransaction($payload, $data['office_id'], $data['user_id'], $data['payment_method_id'], 'interest_posting'));
            }
            return $this->update([
                'balance'=>$new_balance,
                'accrued_interest'=>0
            ]); 

        }
    }
    public function postInterestAll(){
        $list = DepositAccount::listForInterestPosting();
    }

    public function lastTransaction($successful_only=false){
        return $this->transactions($successful_only)->first();
    }

    public function branch(){
        return $this->client->office;
    }

    
}
