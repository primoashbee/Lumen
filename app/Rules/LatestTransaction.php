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
        $transactions = Transaction::where('transaction_number',$value)->orderBy('id','desc')->get();
       
       
        foreach ($transactions as $transaction) {
            $type  = $transaction->transactionType();
            if ($type=='Loan') {
                $latest  = LoanAccount::find((int) $transaction->transactionable->loan_account_id)->latestTransaction();
                if (is_null($latest)) {
                    return true;
                }

                $latest  = $latest->created_at;
                $t_date = $transaction->created_at;
                $this->recent_transaction =  $transaction->transaction_number;
                $diff = $t_date->equalTo($latest);
                if ($diff) {
                    return true;
                }

                return $latest->gt($t_date) ? false : true;

            }
            if ($type=='Deposit') {
                $deposit_account_id = (int) $transaction->transactionable->deposit_account_id;
                $latest_transaction = DepositAccount::find($deposit_account_id)->latestTransaction();
                if (is_null($latest_transaction)) {
                    return true;
                }
            
                $latest  = $latest_transaction->created_at;
                $t_date = $transaction->created_at;
                $this->recent_transaction =  $latest_transaction->transaction_number;
                $diff = $t_date->equalTo($latest);
                if ($diff) {
                    return true;
                }

                return $latest->gt($t_date) ? false : true;
            }
        }
        return false;
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
