<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanAccountTopup extends Model
{
    protected $fillable = [
        'loan_account_id',
        'interest_topup',
        'principal_topup',
        'total_topup',
        'paid_by',
        'payment_method_id',
        'topup_date',
        'notes',
        'office_id',
        'disbursed_by',
        'transaction_number',
        'reverted',
        'reverted_by',
        'revertion',
        'office_id'
    ];
    
    protected $dates = ['created_at','updated_at','topup_date','mutated'];
    
    

    public function loanAccount()
    {
        return $this->belongsTo(LoanAccount::class);
    }
    public function transaction(){
        return $this->morphOne(Transaction::class,'transactionable');
    }

    public function topup_installment(){
        return $this->hasMany(LoanAccountTopupInstallment::class);
    }

    public function revert($user_id){
        

        $new_interest = $this->loanAccount->interest - $this->interest_topup;
        $topup_installments = $this->topup_installment;
        $new_interest_balance = $this->loanAccount->interest_balance - $this->interest_topup;
        $new_principal = $this->loanAccount->principal - $this->principal_topup;
        $new_total_loan_amount = $new_interest + $new_principal;

        
        // dd($new_interest);
        $this->loanAccount->update(
            [
                'interest' => $new_interest,
                'principal' => $new_principal,
                'total_loan_amount' => $new_total_loan_amount,
                'amount' => $new_principal,
                'principal_balance' => $new_principal,
                'interest_balance' => $new_interest_balance,
                'total_balance' => $new_interest_balance + $new_principal
            ]
        );


        foreach($topup_installments as $installment){
            $installment->revert($user_id);
            $installment->delete();
        }
        

        $this->update([
            'reverted'=>true,
            'reverted_by'=>$user_id
        ]);
        return true;
    }
}
