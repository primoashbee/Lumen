<?php

namespace App;

use App\Transaction;
use Illuminate\Database\Eloquent\Model;

class DepositWithdrawal extends Model
{
    protected $fillable = ['transaction_number','deposit_account_id','amount','balance','payment_method_id','repayment_date','reverted','reverted_by','revertion','office_id','paid_by','notes'];

    protected $appends =['payment_method_name','formatted_amount','formatted_balance'];

    public function transaction(){
        return $this->morphOne(Transaction::class,'transactionable');
    }
    
    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class);
    }
    public function account(){
        return $this->belongsTo(DepositAccount::class,'deposit_account_id','id');
    }
    public function getPaymentMethodNameAttribute(){
        return PaymentMethod::find($this->payment_method_id)->name;
        // return $this->paymentMethod->name;
    }

    public function getFormattedAmountAttribute(){
        return money($this->amount,2);
    }
    public function getFormattedBalanceAttribute(){
        return money($this->balance,2);
    }
    public function revert($user_id){
        $data = $this->revertData($user_id);
        $this->update([
            'reverted'=>true,
            'reverted_by'=>$user_id
        ]);
        return $this->account->deposit($data,true,true,true);
    }

    public function revertData($user_id){
        
        $data['amount'] = (double) $this->amount;
        $data['transaction_number'] = 'D'. str_replace('.','',microtime(true));
        $data['payment_method_id'] = (int) $this->payment_method_id;
        $data['notes'] = 'Revertion for ' . $this->transaction_number;
        $data['paid_by'] = $user_id;
        $data['office_id'] = (int) $this->office_id;
        $data['repayment_date'] = $this->repayment_date;
        return $data;

    }
    
}
