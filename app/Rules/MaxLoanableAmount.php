<?php

namespace App\Rules;

use App\Loan;
use Illuminate\Contracts\Validation\Rule;

class MaxLoanableAmount implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $loan_id;
    public $max_loanable_amount;
    public function __construct($loan_id)
    {
        $this->loan_id = $loan_id;
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
        $max_loanable_amount = Loan::find($this->loan_id)->loan_maximum_amount;
        $this->max_loanable_amount = $max_loanable_amount;
        return  $max_loanable_amount > $value ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Maximum loanable amount is ' . money($this->max_loanable_amount,2);
    }
}
