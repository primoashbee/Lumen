<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TransactionType implements Rule
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
        $type = ['deposit','withdraw', 'CTLP','Interest Posting','post_interest','repayment','fee_payment'];
        return in_array($value,$type) ?  true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid transaction type';
    }
}
