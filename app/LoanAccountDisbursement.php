<?php

namespace App;

use App\CheckVoucher;
use Illuminate\Database\Eloquent\Model;

class LoanAccountDisbursement extends Model
{
    protected $fillable = [
        'transaction_id',
        'loan_account_id',
        'payment_method_id',
        'disbursed_amount',
        'disbursed_by',
        'reverted',
        'reverted_by',
        'disbursed_amount',
        'disbursed_by'
        
    ];
    
    protected $appends = [
      'mutated'  
    ];

    public function getMutatedAttribute(){
        $mutated['total_paid'] = env('CURRENCY_SIGN') . ' ' . number_format($this->disbursed_amount,2);
        
        $mutated['paid_by'] = $this->paidBy->fullname;
        $mutated['payment_method'] = $this->paymentMethod->name;
            
        
        $mutated['particulars'] = 'Disbursement';
        return $mutated;   
        
    }
    public function cv(){
        return $this->morphOne(CheckVoucher::class,'check_voucherable');
    }
    public function paidBy(){
        return $this->belongsTo(User::class,'disbursed_by');
    }
    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class);
    }
    public function loanAccount(){
        return $this->belongsTo(LoanAccount::class);
    }
    public function fees(){
        return $this->hasMany(LoanAccountFeePayment::class,'loan_account_disbursement_transaction_id','transaction_id');
    }
    public function revert($user_id){

        $feePayments = $this->fees;
        
        foreach($feePayments as $fee){
            $fee->revert($user_id);
        }
        $this->update([
            'reverted'=>true,
            'reverted_by'=>$user_id
        ]);
        return true;
    }


    public static function bulkDisbursement($transaction_id){
        
    }
    
}
