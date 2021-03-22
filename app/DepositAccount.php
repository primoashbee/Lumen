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
    protected $appends = [
        'balance_formatted',
        'new_balance',
        'new_balance_formatted',
        'accrued_interest_formatted',
    ];
    protected $fillable = [
        'client_id',
        'deposit_id',
        'balance',
        'accrued_interest',
        'status',
        'repayment_date',
        'user_id'
    ];
    protected $casts = [
        'created_at' => 'datetime:F d, Y',
    ];
    protected $dates = [
        'created_at',
        'updated_at',

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
            
            //Create Payment
            $payment = $this->payments()->create([
                'amount'=>$data['amount'],
                'balance'=>$new_balance,
                'payment_method_id'=>$data['payment_method']
                // 'notes'=>$data['notes'],
            ]);
            

            //Create Transaction
            if($for_revertion){
                if($from_ctlp){
                    $payment->transaction()->create([
                        'transaction_number'=>$transaction_number,
                        'transaction_date'=>$data['repayment_date'],
                        'type'=>'CTLP Revertion',
                        'office_id'=>$data['office_id'],
                        'posted_by'=>$data['user_id'],
                        'revertion'=>true
                    ]);
                }else{
                    $payment->transaction()->create([
                        'transaction_number'=>$transaction_number,
                        'transaction_date'=>$data['repayment_date'],
                        'type'=>'Withdrawal Revertion',
                        'office_id'=>$data['office_id'],
                        'posted_by'=>$data['user_id'],
                        'revertion'=>true
                    ]);
                }
            }else{
                $payment->transaction()->create([
                    'transaction_number'=>$transaction_number,
                    'transaction_date'=>$data['repayment_date'],
                    'type'=>'Deposit',
                    'office_id'=>$data['office_id'],
                    'posted_by'=>$data['user_id']
                ]);
                $payment->receipt()->create(['receipt_number'=>$data['receipt_number']]);

            }

            $this->balance = $new_balance;
            if($single_payment){
                $payload = ['date'=>$data['repayment_date'],'amount'=>$data['amount']];
                event(new \App\Events\DepositTransaction($payload,$data['office_id'],$data['user_id'],$data['payment_method'],'deposit'));
            }
            
            $this->save();
            
    }

    public function withdraw(array $data, $single_withdraw =false, $for_revertion = false){
        if (!$for_revertion) {
            if ($this->getRawOriginal('balance') < $data['amount']) {
                return false;
            }
        }
        $new_balance = $this->getRawOriginal('balance') - $data['amount'];

        // \DB::beginTransaction();
        // try{
            $transaction_number = uniqid();
            $new_balance = $this->getRawOriginal('balance') - $data['amount'];
            
            //Create Payment
            $withdrawal = $this->withdrawals()->create([
                'amount'=>$data['amount'],
                'balance'=>$new_balance,
                'payment_method_id'=>$data['payment_method'],
                'notes'=>$data['notes']
            ]);

            // $withdrawal->receipt()->create(['receipt_number'=>$data['receipt_number']]);

            //Create Transaction
            
            if($for_revertion){
                $withdrawal->transaction()->create([
                    'transaction_number'=>$transaction_number,
                    'transaction_date'=>$data['repayment_date'],
                    'type'=>'Deposit Revertion',
                    'office_id'=>$data['office_id'],
                    'posted_by'=>$data['user_id'],
                    'revertion'=>true
                ]);
            }else{
                $withdrawal->transaction()->create([
                    'transaction_number'=>$transaction_number,
                    'transaction_date'=>$data['repayment_date'],
                    'type'=>'Withdrawal',
                    'office_id'=>$data['office_id'],
                    'posted_by'=>$data['user_id']
                ]);
            }


            $this->balance = $new_balance;


            $payload = ['date'=>$data['repayment_date'],'amount'=>$data['amount']];
            if ($single_withdraw && !$for_revertion) {
                event(new \App\Events\DepositTransaction($payload, $data['office_id'], $data['user_id'], $data['payment_method'], 'withdraw'));
            }
            $this->balance = $new_balance;
            $this->save();
            \DB::commit();

        // }catch(Exception $e){

        // }
    }

    public function payCTLP(array $data, $single_payment=false){
  
        if ($this->getRawOriginal('balance') < $data['amount']) {
            return false;
        }
        $new_balance = $this->getRawOriginal('balance') - $data['amount'];

      

        $withdrawal = $this->withdrawals()->create([
            'amount'=>$data['amount'],
            'balance'=>$new_balance,
            'payment_method_id'=>$data['payment_method'],
            'notes'=>$data['notes'],
        ]);

        $this->balance = $new_balance;
        $this->save();
        // DepositTransaction::create([
        //     'transaction_id' => uniqid(),
        //     'deposit_account_id' => $this->id,
        //     'transaction_type'=>'CTLP',
        //     'amount'=>$data['amount'],
        //     'payment_method'=>$data['payment_method'],
        //     'repayment_date'=>$data['repayment_date'],
        //     'user_id'=> $data['user_id'],
        //     'balance' => $new_balance
        // ]);
    
        $payload = ['date'=>$data['repayment_date'],'amount'=>$data['amount']];
        if ($single_payment) {
            event(new \App\Events\DepositTransaction($payload, $data['office_id'], $data['user_id'], $data['payment_method'], 'CTLP'));
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
            'payment_method'=>$data['payment_method'],
            'repayment_date'=>$data['repayment_date'],
            'user_id'=> $data['user_id'],
            'balance' => $new_balance
        ]);
            
        
        $this->balance = $new_balance;
        return $this->save();
    }


    
    public function transactions(){
        // Transaction::whereHas('transactionable',function($q){
        //     $q->
        // });
        return Transaction::depositAccountTransactions($this->id);
        // return $this->hasMany(DepositTransaction::class)->orderBy('created_at','desc');
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

    public function latestTransaction(){
        $transactions = $this->transactions->where('reverted',0)->where('revertion',false);
        if($transactions->count() > 0){
            return $transactions->sortByDesc('id')->first();
        }
        return null;
        
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

    // public function postInterest($single_posting = false){
    //     $current_balance = $this->getRawOriginal('balance');
    //     $accrued_interest = $this->getRawOriginal('accrued_interest');
    //     $new_balance = $current_balance + $accrued_interest;
    //     $this->accrued_interest = 0;
    //     $this->balance = $new_balance;
    //     if ($accrued_interest > 0) {

    //         $this->transactions()->create([
    //             'transaction_id' => uniqid(),
    //             'transaction_type'=>'Interest Posting',
    //             'amount'=>$accrued_interest,
    //             'payment_method'=>$this->branch()->defaultPaymentMethods()['for_deposit'],
    //             'repayment_date'=>Carbon::now(),
    //             'user_id'=> 1,
    //             'balance' => $new_balance
    //         ]);

    //     }else{
    //         return false;
    //     }
        
    //     $repayment = now()->toDateString();
    //     $payload = ['date'=>$repayment,'amount'=>$accrued_interest];
    //     if ($single_posting) {
    //         event(new \App\Events\DepositTransaction($payload, $data['office_id'], $data['user_id'], $data['payment_method'], 'withdraw'));
    //     }
    //     return $this->save();
    // }
    public function postInterest(array $data, $single_posting=false){   
        $current_balance = $this->getRawOriginal('balance');
        $accrued_interest = $this->getRawOriginal('accrued_interest');
        $new_balance = $current_balance + $accrued_interest;
        $this->accrued_interest = 0;
        $this->balance = $new_balance;

        $transaction_number = uniqid();
        $data['payment_method'] = PaymentMethod::interestPosting()->id;
        if ($accrued_interest > 0) {
            $posting = $this->interestPostings()->create([
                    'amount'=>$accrued_interest,
                    'payment_method_id'=>$data['payment_method'],
                    // 'repayment_date'=>Carbon::now(),
                    // 'user_id'=> $data['user_id'],
                    'balance' => $new_balance
                ]);
            $posting->transaction()->create([
                    'type'=>'Interest Post',
                    'office_id'=>$data['office_id'],
                    'posted_by'=>$data['user_id'],
                    'transaction_number'=>$transaction_number,
                    'transaction_date'=>now()
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
                event(new \App\Events\DepositTransaction($payload, $data['office_id'], $data['user_id'], $data['payment_method'], 'interest_posting'));
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
    
    public function getStatusAttribute($value){
        return ucwords($value);
    }
    public function getAmountAttribute(){
        return 0;
    }

    public function getAccruedInterestAttribute($value){
        return round($value,4); 
    }

    public function getNewBalanceAttribute(){
        return round($this->getRawOriginal('balance') + $this->getRawOriginal('accrued_interest'),4);
    }
    public function getNewBalanceFormattedAttribute(){
        return env('CURRENCY_SIGN')." ".number_format(round($this->getRawOriginal('balance') + $this->getRawOriginal('accrued_interest'),4),2);
    }
    public function getRawBalanceAttribute(){
        return round($this->getRawOriginal('balance'),2);   
    }
    public function getAccruedInterestFormattedAttribute(){
        return  env('CURRENCY_SIGN')." ".round($this->getRawOriginal('accrued_interest'),4);   
    }

    public function lastTransaction(){
        return Transaction::depositAccountTransactions($this->id)->orderBy('created_at','desc')->first();
    }

    public function getTransactionsAttribute(){
        return Transaction::depositAccountTransactions($this->id)->orderBy('created_at','desc')->get();
    }

    public function branch(){
        return $this->client->office;
    }

    public function account(){
        return $this->morphOne(Account::class, 'accountable');
    }

    
}
