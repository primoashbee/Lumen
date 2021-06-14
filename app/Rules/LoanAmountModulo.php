<?php

namespace App\Rules;

use App\Loan;
use Illuminate\Contracts\Validation\Rule;

class LoanAmountModulo implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $loan_id;
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
        $loan = Loan::findOrFail($this->loan_id);

        if($loan->isDRP()){
            return true;
        }else{
            return $value % 1000 == 0;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Loan Amount should be divisible by 1000';
    }
}
