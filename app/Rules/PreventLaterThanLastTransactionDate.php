<?php

namespace App\Rules;

use Carbon\Carbon;
use App\DepositAccount;
use Illuminate\Contracts\Validation\Rule;

class PreventLaterThanLastTransactionDate implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $deposit;
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
        if($this->deposit->lastTransaction() == null){
            return true;
        }

        $last_transaction_date = $this->deposit->lastTransaction()->transaction_date;
        
        $diff = $last_transaction_date->diffInDays(Carbon::parse($value)->startOfDay(),false);
        if($diff  < 0){
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Repayment date should not be earlier than '.$this->deposit->lastTransaction()->transaction_date->format('F d, Y');
    }
}
