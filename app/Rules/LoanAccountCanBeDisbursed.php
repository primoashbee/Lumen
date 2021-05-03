<?php

namespace App\Rules;

use App\LoanAccount;
use Illuminate\Contracts\Validation\Rule;

class LoanAccountCanBeDisbursed implements Rule
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
        return LoanAccount::find($value)->canBeDisbursed();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This loan account cannot be disbursed';
    }
}
