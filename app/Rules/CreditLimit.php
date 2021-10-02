<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CreditLimit implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $credit_limit;
    public $loan_amount;

    public function __construct($credit_limit, $loan_amount)
    {
        $this->credit_limit = $credit_limit;
        $this->loan_amount = $loan_amount;
        
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
        
        return $this->loan_amount <= $this->credit_limit ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The loan amount that can avail is up to '. money($this->credit_limit, 2);
    }
}
