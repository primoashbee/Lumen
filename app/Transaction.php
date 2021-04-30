<?php

namespace App;

use App\DepositToLoanRepayment;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['transaction_number','type','transactionable_id','transactionable_type','office_id','transaction_date','posted_by','reverted','reverted_by','reverted_at','revertion'];
    public $deposit_transactions = ['App\DepositPayment','App\DepositWithdrawal','App\DepositInterestPost'];
    public static $deposit_transactions_report = ["Payment","Withdrawal","CTLP Withdrawal","Interest Posting"];
    public $loan_transactions = ['App\LoanAccountFeePayment','App\LoanAccountRepayment','App\DepositToLoanRepayment'];
    
    protected $dates = ['transaction_date','created_at','updated_at','reverted_at'];
    protected $appends = ['user_name'];
    protected $casts = [
        'reverted'=>'boolean',
        'revertion'=>'boolean'
    ];
    public function transactionable(){
        return $this->morphTo();
    }
    public static function depositAccountTransactions($deposit_account_id){

        return Transaction::whereHasMorph(
                'transactionable',
                Transaction::getTransactionType('deposit'),
                function($q) use ($deposit_account_id){
                    // $q->select('*');
                    $q->where('deposit_account_id',$deposit_account_id);
                }
            )
            ->with('transactionable');
    }
    public static function loanAccountTransactions($loan_account_id,$loan_payment_only=false){
        $transactionbles = Transaction::getTransactionType('loan',$loan_payment_only);
        return Transaction::whereHasMorph(
            'transactionable', 
            $transactionbles, 
            function($q) use($loan_account_id){
                // $q->whereIn('type',['CTLP','Loan Payment']);
                $q->where('loan_account_id',$loan_account_id);
            })->with('transactionable');
    }


    public static function getTransactionType($type=null, $loan_payment_only=false){
        $t = new Transaction;

        if($type=="deposit"){
            return $t->deposit_transactions;

        }
        if($type=="loan"){
            if($loan_payment_only){
                return ['App\LoanAccountRepayment','App\DepositToLoanRepayment'];
            }
            return $t->loan_transactions;

        }
        return collect($t->loan_transactions)->merge($t->deposit_transactions);

    }

    public function transactionType(){
        $t = new Transaction;
        if(in_array($this->transactionable_type,$t->loan_transactions)){
            return 'Loan';
        }
        if(in_array($this->transactionable_type,$t->deposit_transactions)){
            return 'Deposit';
        }
    }

    public function getUserNameAttribute(){
        return User::select('firstname','lastname')->find($this->posted_by);
    }

    public function revert($user_id){
        $this->update([
            'reverted'=>true,
            'reverted_by'=>$user_id,
            'reverted_at'=>now()
        ]);
        $transaction = $this->transactionable;
        return $transaction->revert($user_id);
         
    }

    public static function report(array $data, $type){
        
        if($type=='repayments'){
            $types = ['CTLP','Loan Payment','Fee Payment'];
            
            return Transaction::select('id','office_id','transaction_number','transactionable_id','transactionable_type')
                ->whereIn('type',$types)
                ->when($data['from_date'], function($q) use ($data){
                    $q->when($data['to_date'], function($q2) use ($data){
                        $q2->whereBetween('transaction_date',[$data['from_date'], $data['to_date']]);
                    });
                })
                ->when($data['office_id'], function($q) use ($data){
                    $q->where('office_id',$data['office_id']);
                })
                ->when($data['users'], function($q) use ($data){
                    $q->whereIn('users',$data['users']);
                })
                ->when($data['type'], function($q) use($data){
                    $q->whereIn('type', $data['type']);
                })
                ->with('transactionable.loanAccount.type');
        }
    }

    public static function reportV2(array $data, $type){
        if($type == 'repayments'){
            $payment_type = $data['type'];
            if(in_array($payment_type, $payment_type) && $payment_type == []){
                
            }
        }
    }

    public static function transactionOf($type){

        if(in_array($type, Transaction::$deposit_transactions_report)){
            
            if($type =='Payment'){
                return ['name'=>'Deposit Account Transaction','table'=>'deposit_payments','type'=>'Deposit'];
            }
            
            if($type =='Withdrawal'){
                return ['name'=>'Deposit Account Transaction','table'=>'deposit_withdrawals','type'=>'Withdrawal'];
            }
            if($type =='CTLP Withdrawal'){
                return ['name'=>'Deposit Account Transaction','table'=>'deposit_to_loan_repayments','type'=>'Withdawal - CTLP'];
            }
            if($type =='Interest Posting'){
                return ['name'=>'Deposit Account Transaction','table'=>'deposit_interest_posts','type'=>'Interest Posting'];
            }
            
        }
    }

    public static function type($value){

        if($value==='P'){
            return 'deposit_interest_posts';
        }
        if($value==='D'){
            return 'deposit_payments';
        }
        if($value==='W'){
            return 'deposit_withdrawals';
        }
        if($value==='R'){
            return 'loan_account_repayments';
        }
        if($value==='F'){
            return 'loan_account_fee_payments';
        }
        if($value==='X'){
            return ['deposit_to_loan_repayaments','deposit_withdrawal'];
        }
    }

    public static function getAccount($transaction_number){
        $type = Transaction::type(substr($transaction_number,0,1));
        if($type==='deposit_interest_posts'){
            return DepositInterestPost::where('transaction_number',$transaction_number)->first()->account;
        }
        if($type==='deposit_payments'){
            return DepositPayment::where('transaction_number',$transaction_number)->first()->account;
        }
        if($type==='deposit_withdrawals'){
            return DepositWithdrawal::where('transaction_number',$transaction_number)->first()->account;
        }
        if($type==='loan_account_repayments'){
            return LoanAccountRepayment::where('transaction_number',$transaction_number)->first()->loanAccount;
        }
        if(is_array($type)){
            return [
                DepositToLoanRepayment::where('transaction_number',$transaction_number)->first()->loanAccount,
                DepositWithdrawal::where('transaction_number',$transaction_number)->first()->account
            ];
        }

    }
    public static function get($transaction_number){
        $type = Transaction::type(substr($transaction_number,0,1));
        if($type==='deposit_interest_posts'){
            return DepositInterestPost::where('transaction_number',$transaction_number)->first();
        }
        if($type==='deposit_payments'){
            return DepositPayment::where('transaction_number',$transaction_number)->first();
        }
        if($type==='deposit_withdrawals'){
            return DepositWithdrawal::where('transaction_number',$transaction_number)->first();
        }
        if($type==='loan_account_repayments'){
            return LoanAccountRepayment::where('transaction_number',$transaction_number)->first();
        }
        if(is_array($type)){
            return [
                DepositToLoanRepayment::where('transaction_number',$transaction_number)->first(),
                DepositWithdrawal::where('transaction_number',$transaction_number)->first(),
            ];
        }

    }
}
