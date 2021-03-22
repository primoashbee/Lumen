<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['transaction_number','type','transactionable_id','transactionable_type','office_id','transaction_date','posted_by','reverted','reverted_by','reverted_at','revertion'];
    public $deposit_transactions = ['App\DepositPayment','App\DepositWithdrawal','App\DepositInterestPost'];
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
}
