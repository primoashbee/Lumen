<?php

namespace App\Rules;

use App\Client;
use Illuminate\Contracts\Validation\Rule;

class HasZeroBalance implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $deposit;
    public $msg;
    public function __construct($deposit)
    {
        $this->deposit = $deposit;
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
        $deposit = $this->deposit;
        $this->msg = "";
        if($deposit->balance > 0){
            $this->msg  = 'Deposit Account balance must not greater that 0.';
            return false;
        }
        if($deposit->accrued_interest > 0){
            $this->msg = 'Deposit Account Accrued Interest must not greater than 0.';
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
        
        return $this->msg;
    }
}
