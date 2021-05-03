<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AccountStatus implements Rule
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
        $list  = ["All","Active", "Cancelled", "Closed", "In Arrears", "Inactive", "Matured", "Pending Approval", "Rejected", "Written Off"];
        return in_array($value,$list);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid Account Status';
    }
}
