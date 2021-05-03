<?php

namespace App\Rules;

use App\LoanAccount;
use Illuminate\Contracts\Validation\Rule;

class LoanAccountCanBeApproved implements Rule
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
        return LoanAccount::find($value)->canBeApproved();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Loan Account cannot be approved.';
    }
}
