<?php

namespace App\Rules;

use App\Transaction;
use Illuminate\Contracts\Validation\Rule;

class ValidTransactionNumber implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $transaction_number;
    public function __construct()
    {
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
        $type = Transaction::get($value);
        if(is_null($type)){
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
        return 'The validation error message.';
    }
}
