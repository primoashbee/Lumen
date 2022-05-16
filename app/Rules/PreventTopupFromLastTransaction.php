<?php

namespace App\Rules;

use App\LoanAccount;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Validation\Rule;

class PreventTopupFromLastTransaction implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $loan_account_disbursed_date;
    public function __construct($id)
    {
        $this->loan_account_disbursed_date = LoanAccount::find($id)->disbursed_at;
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
        return $value > $this->loan_account_disbursed_date ? true : false;   
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Disbursement Date should not greater than last transaction date '. Carbon::parse($this->loan_account_disbursed_date)->format('M d, Y');
    }
}
