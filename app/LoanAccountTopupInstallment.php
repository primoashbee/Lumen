<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanAccountTopupInstallment extends Model
{
    protected $fillable = [
        'loan_account_installment_id',
        'loan_account_topup_id',
        'principal_topup',
        'interest_topup',
        'total_topup',
        'topup_by'
    ];

    public function loanAccountInstallment(){
        return $this->belongsTo(LoanAccountInstallment::class,'loan_account_installment_id');
    }

    public function revert(){
        $installment = $this->loanAccountInstallment;
        
        $deducted_interest = $this->interest_topup;
        $deducted_principal = $this->principal_topup;
        $amortization = round($installment->original_interest - $deducted_interest);
        $new_interest = round($installment->original_interest - $deducted_interest);
        $new_principal = round($installment->original_principal - $deducted_principal);
        
        $principal_balance = $installment->principal > 0 ? $installment->principal - $deducted_principal : 0;
        $interest_balance = $installment->loanAccount->interest - $new_interest * $installment->installment;

        $date_is_due = $installment->dateIsDue(); 

        $new_amount_due = round($new_interest + $new_principal, 2); 

        if ($date_is_due && !$installment->paid) {
            $installment->update(
                [
                    'principal_due'=>$new_principal,
                    'amount_due'=>$new_amount_due,
                    'interest_due' => $new_interest
                ]
            );
        }

        return $installment->update([
            'principal' => $new_principal,
            'interest'=>$new_interest,
            'original_principal' => $new_principal,
            'original_interest' => $new_interest,
            'amortization' => $amortization,
            'interest_balance' => $interest_balance,
            'principal_balance' => $principal_balance
        ]);
        


    }
}
