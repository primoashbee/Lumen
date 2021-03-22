<?php

namespace App;

use App\Transaction;
use Illuminate\Database\Eloquent\Model;

class DepositWithdrawal extends Model
{
    protected $fillable = ['deposit_account_id','amount','balance','payment_method_id','notes'];

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
        
        return $this->account->deposit($data,true,true,true);
    }

    public function revertData($user_id){
        
        $data['amount'] = (int) $this->amount;
        $data['transaction_number'] = uniqid();
        $data['payment_method'] = (int) $this->payment_method_id;
        $data['notes'] = 'Revertion for ' . $this->transaction->transaction_number;
        $data['user_id'] = $user_id;
        $data['office_id'] = (int) $this->transaction->office_id;
        $data['repayment_date'] = $this->transaction->transaction_date;
        return $data;

    }
    
}
