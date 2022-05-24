<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepositToLoanInstallmentRepayment extends Model
{
    protected $fillable =[
        'loan_account_installment_id',
        'deposit_to_loan_repayment_id',
        'principal_paid',
        'interest_paid',
        'total_paid',
        'paid_by',
        'deposit_account_id',
        // 'transaction_id',
    ];



    public function transaction(){
        return $this->moprhOne(Transaction::class,'transactionable');
    }

    public function jv(){
        return $this->morphOne(JournalVoucher::class,'journal_voucherable');
    }

    public function revert(){
        $installment = $this->installment;

        $returned_interest = $this->interest_paid;
        $returned_principal = $this->principal_paid;
        

        $new_interest = round($installment->interest + $returned_interest,2);
        $new_principal = round($installment->principal_due + $returned_principal,2);

        $date_is_due = $installment->dateIsDue();
        if($date_is_due){
            $new_interest = round($installment->interest_due + $returned_interest,2);
        }

        $new_amount_due = round($new_interest + $new_principal, 2);

        if($date_is_due){
            return $installment->update([
                'interest_due'=>$new_interest,
                'principal_due'=>$new_principal,
                'amount_due'=>$new_amount_due,
                'paid'=>false,
            ]);
        }

        return $installment->update([
            'interest'=>$new_interest,
            'principal_due'=>$new_principal,
            'amount_due'=>$new_amount_due,
            'paid'=>false,
        ]);
    }
    public function installment(){
        return $this->belongsTo(LoanAccountInstallment::class,'loan_account_installment_id');
    }
}
