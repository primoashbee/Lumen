<?php

namespace App\Rules;

use App\DepositAccount;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Validation\Rule;

class ValidDepositTransactionDate implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $deposit;
    protected $last_transaction_date;

    public function __construct($deposit_id)
    {
        $this->deposit = DepositAccount::find($deposit_id);

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
        $transaction = $this->deposit->lastTransaction(true);
        if(is_null($transaction)){
            return true;
        }
        $date  = Carbon::parse($value)->startOfDay();
        $transaction = Carbon::parse($transaction->repayment_date)->startOfDay();
        $this->last_transaction_date= $transaction;
        return $transaction->diffInDays($date,false) < 0 ? false : true;
        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Date should not be earlier than the last transaction date, '.$this->last_transaction_date->format('F d, Y');
    }
}
