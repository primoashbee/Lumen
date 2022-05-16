<?php

namespace App;

use App\LoanAccountInstallment;
use Illuminate\Database\Eloquent\Model;

class LoanAccountInstallmentRepayment extends Model
{
    protected $fillable =[
        'loan_account_installment_id',
        'loan_account_repayment_id',
        'principal_paid',
        'penalty_paid',
        'interest_paid',
        'total_paid',
        'paid_by',
        'transaction_id'
    ];

    public function repaymentable(){
        $this->morphTo();
    }
    public function installment(){
        return $this->belongsTo(LoanAccountInstallment::class,'loan_account_installment_id');
    }

    public function revert(){

        //get amount to be returned
        $installment = $this->installment;

        $returned_interest = $this->interest_paid;
        $returned_principal = $this->principal_paid;
        

        $new_interest = round($installment->interest + $returned_interest,2);
        $new_principal = round($installment->principal_due + $returned_principal,2);
        $new_penalty = round($installment->penalty + $this->penalty_paid,2);

        $date_is_due = $installment->dateIsDue();
        if($date_is_due){
            $new_interest = round($installment->interest_due + $returned_interest,2);
        }

        $new_amount_due = round($new_interest + $new_principal + $new_penalty,2);

        if($date_is_due){
            return $installment->update([
                'interest_due'=>$new_interest,
                'principal_due'=>$new_principal,
                'penalty' => $new_penalty,
                'amount_due'=>$new_amount_due,
                'paid'=>false,
            ]);
        }

        return $installment->update([
            'interest'=>$new_interest,
            'principal_due'=>$new_principal,
            'penalty' => $this->penalty_paid,
            'amount_due'=>$new_amount_due,
            'paid'=>false,
        ]);
    }
}
