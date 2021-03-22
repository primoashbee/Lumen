<?php

namespace App;

use App\PaymentMethod;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class LoanAccountRepayment extends Model
{
    // use MoneyMutator;

    protected $fillable = [
        'loan_account_id',
        'interest_paid',
        'principal_paid',
        'total_paid',
        'carried_over_amount',
        'paid_by',
        'payment_method_id',
        'repayment_date',
        'for_pretermination',
        'notes',
        // 'transaction_id',
        // 'reverted',
        // 'reverted_by',
    ];
    
    protected $dates = ['created_at','updated_at','repayment_date','mutated'];
    protected $appends = ['mutated'];
    protected $for_mutation =['interest_paid','total_paid','principal_paid'];

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class);
    }
    public function loanAccount(){
        return $this->belongsTo(LoanAccount::class);
    }

    public function paidBy(){
        return $this->belongsTo(User::class,'paid_by');
    }
    public function getMutatedAttribute(){
        $fields = $this->for_mutation;
        
        foreach($fields as $field){
            $attribute = $field;
            $mutated[$attribute] = env('CURRENCY_SIGN') . ' ' . number_format($this->$field,2);
        }
        $mutated['particulars'] = 'Loan Repayment';
        if($this->for_pretermination){
            $mutated['particulars'] = 'Pre-Termination';
        }
        
        $mutated['paid_by'] = $this->paidBy->fullname;
        $mutated['payment_method'] = $this->paymentMethod->name;

        return $mutated;
        
    }

    public function revert($user_id){
        //if already reverted

        //if has transaction before


        //delete installment repayments



        if($this->reverted){
            return false;
        }
        if($this->hasTransactionBefore()){
            return false;
        }


        //get installment repayments
        $installments = $this->repayments()->orderBy('id','desc')->get();
        //revert installment repayments
        $total = $installments->count();
        $ctr = 0;
        foreach($installments as $item){
            $item->revert();
            $item->delete();
            $ctr++;
        }
        //update balances
        $this->receipt->delete();
        $this->loanAccount->updateStatus();
        return $total == $ctr;
       
       
       
    }
    
   
    public function hasTransactionBefore(){
        $id = $this->id;
        $loan_account_id = $this->loan_account_id;
        $transactions = Transaction::loanAccountTransactions($this->loan_account_id)
                    ->where('transaction_date',$this->repayment_date->addDay())
                    ->where('reverted',false);
                   

        // $transactions = LoanAccountRepayment::where('loan_account_id',$loan_account_id)->where('reverted',false)->orderBy('id','desc');

        if($transactions->count() > 0){
            
            return $transactions->first()->id  > $this->id ? true : false;
        }
        //has no transaction or first transaction
        return false;

    }

    public function revertPreTermination($user_id){
        if($this->reverted){
            return false;
        }
        if($this->hasTransactionBefore()){
            return false;
        }


        //get installment repayments
        $installments = $this->repayments()->orderBy('id','desc')->get();
        //revert installment repayments
        foreach($installments as $item){
            $item->revert();
            $item->delete();
        }
        //update balances

        
        $this->receipt->delete();

        return $this->update([
            'reverted'=>true,
            'reverted_by'=>$user_id
        ]);
        
        
    }

    public function transaction(){
        return $this->morphOne(Transaction::class,'transactionable');
    }

    public function repayments(){
        return $this->hasMany(LoanAccountInstallmentRepayment::class);
    }

    public function receipt(){
        return $this->morphOne(Receipt::class,'receiptable');
    }
    
}
