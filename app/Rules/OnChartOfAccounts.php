<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OnChartOfAccounts implements Rule
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
        // $accounts = ChartOfAccount::all()->pluck('id')->toArray();
        // in_array($value,$accounts) ? true : false;
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid account selected';
    }
}
