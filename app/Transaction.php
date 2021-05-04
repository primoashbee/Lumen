<?php

namespace App;

use App\DepositToLoanRepayment;

// class Transaction extends Model
class Transaction 
{
    protected $fillable = ['transaction_number','type','transactionable_id','transactionable_type','office_id','transaction_date','posted_by','reverted','reverted_by','reverted_at','revertion'];
    public $deposit_transactions = ['App\DepositPayment','App\DepositWithdrawal','App\DepositInterestPost'];
    public static $deposit_transactions_report = ["Payment","Withdrawal","CTLP Withdrawal","Interest Posting"];
    public $loan_transactions = ['App\LoanAccountFeePayment','App\LoanAccountRepayment','App\DepositToLoanRepayment'];


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

    public function transactionList($type){
        if($type == 'loan'){
            $data = [['type'=>'Transaction' , 'data'=>[['id'=>1,'name'=>'Loan Payment'],['id'=>2,'name'=>'CTLP']]]];
        }
        
        if($type == 'deposit'){
            $data = [['type'=>'Transaction' , 'data'=>[['id'=>1,'name'=>'Payment'],['id'=>2,'name'=>'Withdrawal'],['id'=>3,'name'=>'CTLP Withdrawal'],['id'=>4,'name'=>'Interest Posting']]]];
        }

        return $data;
    }
}
