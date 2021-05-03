<?php

namespace App\Rules;

use App\DepositAccount;
use Illuminate\Contracts\Validation\Rule;

class DepositAccountActive implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
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
        
        return strtoupper(DepositAccount::find($value)->status) == 'ACTIVE' ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Account not valid for this transaction. Must be an active account';
    }
}
