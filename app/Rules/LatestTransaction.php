<?php

namespace App\Rules;

use App\LoanAccount;
use App\Transaction;
use App\DepositAccount;
use Illuminate\Contracts\Validation\Rule;

class LatestTransaction implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $recent_transaction;
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {   
       
        // $transaction = Transaction::getAccount($value);
        
        $transaction = Transaction::getAccount($value);
        if(is_array($transaction)){
            $loan_passes = false;
            $deposit_passes = false;
            collect($transaction)->each(function($item, $key) use ($value, &$loan_passes, &$deposit_passes){
                $model = get_class($item);
                $last = $item->lastTransaction(true)->transaction_number;
                if ($model == "App\LoanAccount") {
                   
                    if ($item->lastTransaction(true)->transaction_number == $value) {
                        $loan_passes = true;
                    }else{
                        $this->recent_transaction = $item->lastTransaction(true)->transaction_number. ' - Loan';
                    }
                }
                if ($model== "App\DepositAccount") {
                    if ($item->lastTransaction(true)->transaction_number == $value) {
                        $deposit_passes = true;
                    }else{
                        $this->recent_transaction = $item->lastTransaction(true)->transaction_number. ' - Deposit Account';
                    }
                }
            });
            if($loan_passes && $deposit_passes){
                return true;
            }
            return false;
        }else{
            
            $transaction = Transaction::getAccount($value)->lastTransaction(true);
            
            

            if (!is_null($transaction)) {
                $this->recent_transaction = $transaction->transaction_number;
                return $transaction->transaction_number  == $value ? true : false;    
            }else{
                return true;
            }
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Revert most recent transaction first. (' . $this->recent_transaction .')';
    }
}
